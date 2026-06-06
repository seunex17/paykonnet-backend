<?php

use App\Models\User;
use App\Models\VirtualCard;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
