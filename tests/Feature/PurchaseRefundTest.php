<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    $this->user = User::factory()->create(['transfer_pin' => Hash::make('1234'), 'agent_level' => 0]);
    $this->user->creditAdd(30000);
    $this->actingAs($this->user);
    $this->purchaseInputs = [
        'amount' => 1000, 'price' => 1000, 'pin' => '1234',
        'product' => '1', 'phone_no' => '08012345678', 'code' => '100',
        'plan' => 'Test plan', 'service' => 'Test service',
        'smart_card_no' => '1234567890', 'serviceID' => 'ikeja-electric',
        'billersCode' => '1234567890', 'phone' => '08012345678',
        'company' => 'Test betting', 'customer_id' => '123456',
    ];
});

dataset('wallet purchases', [
    'airtime' => ['purchase-airtime'],
    'data without explicit spl' => ['purchase-mobile-data-plan'],
    'flex data' => ['purchase-flex-data-plan'],
    'cable' => ['purchase-cable-subscription'],
    'electricity' => ['purchase-electricity-bill'],
    'betting' => ['fund-betting-wallet'],
]);

test('provider HTTP errors restore the wallet exactly once', function (string $endpoint) {
    Http::fake(['*' => Http::response(['message' => 'Unavailable'], 503)]);

    $this->postJson('/api/main/'.$endpoint, $this->purchaseInputs)->assertStatus(400);

    expect($this->user->creditBalance())->toBe(30000.0)
        ->and($this->user->credits()->where('type', 'debit')->count())->toBe(1)
        ->and($this->user->credits()->where('type', 'credit')->count())->toBe(2);
    $this->assertDatabaseMissing('transactions', ['status' => 'success']);
    Http::assertSentCount(1);
})->with('wallet purchases');

test('provider rejection restores the wallet', function (string $endpoint) {
    Http::fake(['*' => Http::response(['status' => 'INVALID_AMOUNT', 'code' => '016', 'response_description' => 'FAILED'])]);

    $this->postJson('/api/main/'.$endpoint, $this->purchaseInputs);

    expect($this->user->creditBalance())->toBe(30000.0);
    $this->assertDatabaseMissing('transactions', ['status' => 'success']);
})->with('wallet purchases');

test('unknown provider outcomes are traceable and do not issue an unverified refund', function (string $endpoint) {
    Http::fake(['*' => Http::failedConnection()]);

    $response = $this->postJson('/api/main/'.$endpoint, $this->purchaseInputs)
        ->assertStatus(202)->assertJsonPath('status', 'pending');

    expect($this->user->creditBalance())->toBe(29000.0);
    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'references' => $response->json('references'),
        'status' => 'pending',
        'amount' => '1000',
    ]);
    expect(Transaction::count())->toBe(1);
})->with('wallet purchases');

test('successful purchases keep one debit and no refund', function (string $endpoint) {
    Http::fake(['*' => Http::response([
        'status' => 'ORDER_RECEIVED', 'code' => '000',
        'response_description' => 'TRANSACTION SUCCESSFUL',
        'content' => ['transactions' => ['type' => 'Purchase', 'product_name' => 'Electricity']],
        'purchased_code' => '1234',
    ])]);

    $this->postJson('/api/main/'.$endpoint, $this->purchaseInputs)->assertOk();

    expect($this->user->creditBalance())->toBe(29000.0)
        ->and($this->user->credits()->where('type', 'debit')->count())->toBe(1)
        ->and($this->user->credits()->where('type', 'credit')->count())->toBe(1);
    $this->assertDatabaseHas('transactions', ['user_id' => $this->user->id, 'status' => 'success']);
})->with('wallet purchases');

test('failed sell pay later purchase does not add wallet credits or create debt', function () {
    Http::fake(['*' => Http::response([], 503)]);

    $this->postJson('/api/main/purchase-mobile-data-plan', [...$this->purchaseInputs, 'spl' => 'yes'])->assertStatus(400);

    expect($this->user->creditBalance())->toBe(30000.0)
        ->and($this->user->credits()->count())->toBe(1);
    $this->assertDatabaseCount('sell_pay_laters', 0);
});

test('agent data failure refunds the discounted debit', function () {
    $this->user->update(['agent_level' => 1]);
    Http::fake(['*' => Http::response([], 503)]);

    $this->postJson('/api/main/purchase-mobile-data-plan', $this->purchaseInputs)->assertStatus(400);

    expect($this->user->creditBalance())->toBe(30000.0)
        ->and((float) $this->user->credits()->where('type', 'debit')->sole()->amount)->toBe(950.0);
});

test('invalid payment option cannot bypass the wallet debit', function () {
    $this->postJson('/api/main/purchase-mobile-data-plan', [...$this->purchaseInputs, 'spl' => 'invalid'])->assertStatus(400);

    expect($this->user->creditBalance())->toBe(30000.0);
    Http::assertNothingSent();
});

test('same second purchases have different provider references', function () {
    $this->freezeTime();
    Http::fake(['*' => Http::response(['status' => 'ORDER_COMPLETED'])]);

    $first = $this->postJson('/api/main/purchase-airtime', $this->purchaseInputs)->assertOk();
    $second = $this->postJson('/api/main/purchase-airtime', $this->purchaseInputs)->assertOk();

    expect($first->json('references'))->not->toBe($second->json('references'))
        ->and($this->user->creditBalance())->toBe(28000.0);
});
