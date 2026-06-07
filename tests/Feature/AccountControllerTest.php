<?php

use App\Mail\ResetLockscreenPintokenMail;
use App\Mail\ResetTransferPinTokenMail;
use App\Models\SellPayLater;
use App\Models\SubAgent;
use App\Models\User;
use App\Models\VirtualCard;
use App\Models\Wallet;
use App\Services\FlutterwaveService;
use App\Services\MonoService;
use App\Services\OnesignalService;
use App\Services\QoreIdService;
use Ichtrojan\Otp\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can retrieve user wallets', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/wallets');

    $response->assertOk()
        ->assertJson([
            'account_number' => $wallet->account_number,
            'bank_name' => $wallet->bank_name,
            'references' => $wallet->references,
            'main_balance' => $user->creditBalance(),
        ]);
});

test('it returns unauthorized if user is not logged in', function () {
    $response = $this->getJson('/api/account/wallets');

    $response->assertUnauthorized();
});

test('it can retrieve user virtual card', function () {
    $user = User::factory()->create();
    $card = VirtualCard::factory()->create([
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/virtual-card');

    $response->assertOk()
        ->assertJson([
            'id' => $card->id,
            'card_id' => $card->card_id,
            'user_id' => $user->id,
            'is_active' => true,
        ]);
});

test('it returns empty array if no active virtual card found', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/virtual-card');

    $response->assertOk()
        ->assertExactJson([]);
});

test('it does not return inactive virtual card', function () {
    $user = User::factory()->create();
    VirtualCard::factory()->create([
        'user_id' => $user->id,
        'is_active' => false,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/virtual-card');

    $response->assertOk()
        ->assertExactJson([]);
});

test('it can retrieve virtual card details from flutterwave', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cardId = '123456';
    $mockData = [
        'status' => 'success',
        'message' => 'Virtual card retrieved',
        'data' => [
            'id' => $cardId,
            'card_pan' => '412345XXXXXX1234',
            'masked_pan' => '412345******1234',
            'city' => 'San Francisco',
            'state' => 'CA',
            'address_1' => '123 Market St',
        ],
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*' => Http::response($mockData, 200),
    ]);

    $response = $this->getJson("/api/account/virtual-card/$cardId");

    $response->assertOk()
        ->assertJson($mockData['data']);
});

test('it returns 404 if virtual card is not found on flutterwave', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cardId = 'non-existent';
    $mockData = [
        'status' => 'error',
        'message' => 'Virtual card not found',
        'data' => null,
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*' => Http::response($mockData, 404),
    ]);

    $response = $this->getJson("/api/account/virtual-card/$cardId");

    $response->assertNotFound()
        ->assertJson(['message' => 'Can not load card']);
});

test('it can retrieve virtual card transactions from flutterwave', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cardId = '123456';
    $mockData = [
        'status' => 'success',
        'message' => 'Card transactions retrieved',
        'data' => [
            [
                'id' => 1,
                'amount' => 10,
                'currency' => 'USD',
                'status' => 'successful',
            ],
        ],
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/transactions*' => Http::response($mockData, 200),
    ]);

    $response = $this->getJson("/api/account/virtual-card/$cardId/transactions");

    $response->assertOk()
        ->assertJson($mockData['data']);
});

test('it returns 404 if virtual card transactions cannot be loaded', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cardId = 'non-existent';
    $mockData = [
        'status' => 'error',
        'message' => 'Virtual card not found',
        'data' => null,
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/transactions*' => Http::response($mockData, 404),
    ]);

    $response = $this->getJson("/api/account/virtual-card/$cardId/transactions");

    $response->assertNotFound()
        ->assertJson(['message' => 'Can not load card']);
});

test('it can validate bvn and generate account number', function () {
    $user = User::factory()->create(['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com']);
    Sanctum::actingAs($user);

    $payload = [
        'title' => 'Mr',
        'phone_no' => '08012345678',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
        'address' => '123 Test St',
        'state' => 'Lagos',
    ];

    $mockResponse = [
        'status' => 'Successful',
        'data' => [
            'provider_response' => [
                'account_number' => '1234567890',
                'bank_name' => 'Fidelity Bank',
                'references' => 'REF123',
            ],
        ],
    ];

    Http::fake([
        'api.paygateplus.ng/v2/transact' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson('/api/account/validate-bvn', $payload);

    $response->assertOk()
        ->assertJson(['message' => 'Your account number has been generated!']);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->id,
        'account_number' => '1234567890',
        'bank_name' => 'Fidelity Bank',
    ]);
});

test('it returns 404 if bvn validation fails requirements', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'title' => 'Mr',
        // 'phone_no' is missing
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
        'address' => '123 Test St',
        'state' => 'Lagos',
    ];

    $response = $this->postJson('/api/account/validate-bvn', $payload);

    $response->assertNotFound()
        ->assertJsonStructure(['message']);
});

test('it returns 404 if fidelity service fails', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'title' => 'Mr',
        'phone_no' => '08012345678',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
        'address' => '123 Test St',
        'state' => 'Lagos',
    ];

    $mockResponse = [
        'status' => 'Failed',
        'message' => 'Invalid BVN details',
    ];

    Http::fake([
        'api.paygateplus.ng/v2/transact' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson('/api/account/validate-bvn', $payload);

    $response->assertNotFound()
        ->assertJson(['message' => 'Invalid BVN details']);
});

test('it can change account password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);
    Sanctum::actingAs($user);

    $payload = [
        'old_password' => 'old-password',
        'new_password' => 'new-secure-password',
        'confirm_password' => 'new-secure-password',
    ];

    $response = $this->postJson('/api/account/change-account-password', $payload);

    $response->assertOk()
        ->assertJson(['message' => 'Password has been changed successfully!']);

    $user->refresh();
    expect(Hash::check('new-secure-password', $user->password))->toBeTrue();
});

test('it returns 404 if old password does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);
    Sanctum::actingAs($user);

    $payload = [
        'old_password' => 'wrong-password',
        'new_password' => 'new-secure-password',
        'confirm_password' => 'new-secure-password',
    ];

    $response = $this->postJson('/api/account/change-account-password', $payload);

    $response->assertNotFound()
        ->assertJson(['message' => 'Old password does not match']);
});

test('it returns 404 if password change validation fails', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Test password mismatch
    $payload = [
        'old_password' => 'password',
        'new_password' => 'new-password',
        'confirm_password' => 'mismatch',
    ];

    $response = $this->postJson('/api/account/change-account-password', $payload);

    $response->assertNotFound()
        ->assertJsonStructure(['message']);
});

test('it can request transfer pin reset token', function () {
    Mail::fake();

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/request-transfer-pin-reset-token');

    $response->assertOk()
        ->assertJson(['message' => 'We have sent a token to your email']);

    Mail::assertSent(ResetTransferPinTokenMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('it returns unauthorized if user is not logged in when requesting reset token', function () {
    $response = $this->postJson('/api/account/request-transfer-pin-reset-token');

    $response->assertUnauthorized();
});

test('it can change transfer pin with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'transfer_pin' => Hash::make('1111'),
    ]);
    Sanctum::actingAs($user);

    $otp = (new Otp)->generate($user->email, 'numeric', 4, 10);

    $payload = [
        'token' => $otp->token,
        'pin' => '2222',
    ];

    $response = $this->postJson('/api/account/change-transfer-pin', $payload);

    $response->assertOk()
        ->assertJson(['message' => 'Transfer pin has been reset successfully!']);

    $user->refresh();
    expect(Hash::check('2222', $user->transfer_pin))->toBeTrue();
});

test('it returns error if transfer pin reset token is invalid', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    Sanctum::actingAs($user);

    $payload = [
        'token' => 'invalid-token',
        'pin' => '2222',
    ];

    $response = $this->postJson('/api/account/change-transfer-pin', $payload);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Reset token is invalid']);
});

test('it returns unauthorized if user is not logged in when changing transfer pin', function () {
    $response = $this->postJson('/api/account/change-transfer-pin', [
        'token' => 'some-token',
        'pin' => '1234',
    ]);

    $response->assertUnauthorized();
});

test('it can request lockscreen pin reset token', function () {
    Mail::fake();

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/request-lockscreen-pin-reset-token');

    $response->assertOk()
        ->assertJson(['message' => 'We have sent a token to your email']);

    Mail::assertSent(ResetLockscreenPintokenMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('it returns unauthorized if user is not logged in when requesting lockscreen reset token', function () {
    $response = $this->postJson('/api/account/request-lockscreen-pin-reset-token');

    $response->assertUnauthorized();
});

test('it can change lockscreen pin with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'lockscreen' => Hash::make('1111'),
    ]);
    Sanctum::actingAs($user);

    $otp = (new Otp)->generate($user->email, 'numeric', 4, 10);

    $payload = [
        'token' => $otp->token,
        'pin' => '2222',
    ];

    $response = $this->postJson('/api/account/change-lockscreen-pin', $payload);

    $response->assertOk()
        ->assertJson(['message' => 'Lockscreen pin has been reset successfully!']);

    $user->refresh();
    expect(Hash::check('2222', $user->lockscreen))->toBeTrue();
});

test('it returns error if lockscreen pin reset token is invalid', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    Sanctum::actingAs($user);

    $payload = [
        'token' => 'invalid-token',
        'pin' => '2222',
    ];

    $response = $this->postJson('/api/account/change-lockscreen-pin', $payload);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Reset token is invalid']);
});

test('it returns unauthorized if user is not logged in when changing lockscreen pin', function () {
    $response = $this->postJson('/api/account/change-lockscreen-pin', [
        'token' => 'some-token',
        'pin' => '1234',
    ]);

    $response->assertUnauthorized();
});

test('it can delete account', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/delete-account');

    $response->assertOk()
        ->assertJson(['message' => 'Account has been deleted!']);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('it can withdraw from virtual card', function () {
    $user = User::factory()->create([
        'transfer_pin' => Hash::make('1234'),
    ]);
    Sanctum::actingAs($user);

    $cardId = 'card_123';
    $amount = 100;

    $mockResponse = [
        'status' => 'success',
        'message' => 'Withdrawal successful',
    ];

    Http::fake([
        "api.flutterwave.com/v3/virtual-cards/$cardId/withdraw" => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson('/api/account/virtual-card/withdraw', [
        'amount' => $amount,
        'pin' => '1234',
        'card_id' => $cardId,
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Fund withdrawal successfully']);

    $user->refresh();
    expect($user->creditBalance())->toEqual((int) ($amount * 100));

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'type' => 'credit',
        'amount' => $amount,
        'note' => 'Withdraw from virtual card',
    ]);
});

test('it can fund wallet and send notification', function () {
    $user = User::factory()->create(['firstname' => 'John']);
    Sanctum::actingAs($user);

    Http::fake([
        'onesignal.com/api/v1/notifications' => Http::response(['id' => 'notif_123'], 200),
    ]);

    $amount = 10000; // Passed to controller
    $response = $this->postJson('/api/account/func-wallet', [
        'ref' => 'REF123',
        'amount' => $amount,
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Your wallet has been topped-up with 100']);

    $user->refresh();
    expect($user->creditBalance())->toEqual($amount); // Stores exactly what is added

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'type' => 'credit',
        'amount' => 100, // 10000 / 100
        'note' => 'Wallet funding',
    ]);
});

test('it can verify id', function () {
    $user = User::factory()->create(['id_verified' => false]);
    Sanctum::actingAs($user);

    Http::fake([
        'api.qoreid.com/token' => Http::response(['accessToken' => 'fake_token'], 200),
        'api.qoreid.com/v1/ng/identities/*' => Http::response(['status' => 'success'], 200),
    ]);

    $response = $this->postJson('/api/account/id-verification', [
        'type' => 'drivers-license',
        'number' => '12345678901',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'ID verification successful!']);

    $user->refresh();
    expect($user->id_verified)->toBeTrue();
    expect($user->id_type)->toBe('drivers-license');
    expect($user->id_number)->toBe('12345678901');
});

test('it can update profile', function () {
    $user = User::factory()->create(['id_verified' => false]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/update-profile', [
        'firstname' => 'UpdatedName',
        'lastname' => 'UpdatedLast',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Your account has been updated successfully!']);

    $user->refresh();
    expect($user->firstname)->toBe('UpdatedName');
    expect($user->lastname)->toBe('UpdatedLast');
});

test('it can upgrade to agent', function () {
    $user = User::factory()->create();
    $user->creditAdd(100000, 'Test credit');
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/upgrade-to-agent', [
        'level' => '1',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Upgrade to agent successful!']);

    $user->refresh();
    expect((int) $user->agent_level)->toBe(1);
    expect($user->creditBalance())->toEqual(0);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'type' => 'debit',
        'amount' => 100000,
        'note' => 'Upgrade to agent',
    ]);
});

test('it can cancel agent subscription', function () {
    $user = User::factory()->create([
        'agent_level' => 1,
        'next_agent_payment_date' => now()->addYear(),
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/cancel-agent-subscription');

    $response->assertOk()
        ->assertJson(['message' => 'Subscription has been canceled']);

    $user->refresh();
    expect((int) $user->agent_level)->toBe(0);
    expect($user->next_agent_payment_date)->toBeNull();
});

test('it can add sub agent', function () {
    $user = User::factory()->create();
    $user->creditAdd(1000, 'Test credit');
    $subAgentUser = User::factory()->create(['email' => 'subagent@example.com']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/add-sub-agent', [
        'agent_level' => '2',
        'email' => 'subagent@example.com',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Sub agent has been added successfully']);

    $user->refresh();
    expect($user->creditBalance())->toEqual(0);

    $this->assertDatabaseHas('sub_agents', [
        'user_id' => $user->id,
        'agent_id' => $subAgentUser->id,
        'agent_level' => '2',
    ]);
});

test('it can connect mono account', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $monoId = 'mono_123';
    $mockResponse = [
        'status' => 'successful',
        'data' => ['id' => $monoId],
    ];

    Http::fake([
        'api.withmono.com/v2/accounts/auth' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson('/api/account/mono/connect', [
        'code' => 'test_code',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Account connected successfully']);

    $user->refresh();
    expect($user->mono_id)->toBe($monoId);
});

test('it can create mono mandate', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'successful',
        'data' => ['mandate_id' => 'mandate_123'],
    ];

    Http::fake([
        'api.withmono.com/v2/payments/initiate' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson('/api/account/mono/create-mandate');

    $response->assertOk()
        ->assertJson(['mandate_id' => 'mandate_123']);
});

test('it can repay owe spl', function () {
    $user = User::factory()->create();
    $user->creditAdd(500, 'Test credit');
    SellPayLater::create([
        'user_id' => $user->id,
        'product' => 'Test Product',
        'amount' => 500,
        'paid' => false,
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/repay-owe-spl');

    $response->assertOk()
        ->assertJson(['message' => 'Transaction successful']);

    $user->refresh();
    expect($user->creditBalance())->toEqual(0);
    expect(SellPayLater::where('user_id', $user->id)->first()->paid)->toBeTrue();
});

test('it returns unauthorized if user is not logged in when deleting account', function () {
    $response = $this->postJson('/api/account/delete-account');

    $response->assertUnauthorized();
});
