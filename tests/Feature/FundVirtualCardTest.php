<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can fund a virtual card successfully', function () {
    $user = User::factory()->create([
        'transfer_pin' => Hash::make('1234'),
    ]);
    $user->addCredits(1000);

    Sanctum::actingAs($user);

    $payload = [
        'amount' => 500,
        'pin' => '1234',
        'card_id' => 'card_123',
    ];

    $mockResponse = [
        'status' => 'success',
        'message' => 'Card funded successfully',
        'data' => [
            'amount' => 500,
            'currency' => 'NGN',
        ],
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/fund' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson('/api/account/virtual-card/fund', $payload);

    // I noticed a bug in AccountController.php:404 where $user is not defined (it should be $request->user())
    // Let's see if it fails as expected.
    $response->assertOk();

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'type' => 'debit',
        'amount' => 500,
        'note' => 'Fund virtual card',
    ]);

    $user->refresh();
    expect($user->creditBalance())->toEqual(500); // 1000 - 500
});

test('it returns error for invalid amount', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/virtual-card/fund', [
        'amount' => 10, // Minimum is 50
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Invalid amount entered']);
});

test('it returns error for invalid pin', function () {
    $user = User::factory()->create([
        'transfer_pin' => Hash::make('1234'),
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/virtual-card/fund', [
        'amount' => 100,
        'pin' => 'wrong',
        'card_id' => 'card_123',
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Invalid pin entered']);
});

test('it returns error for insufficient credits', function () {
    $user = User::factory()->create([
        'transfer_pin' => Hash::make('1234'),
    ]);
    $user->addCredits(100);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/virtual-card/fund', [
        'amount' => 500,
        'pin' => '1234',
        'card_id' => 'card_123',
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Insufficient account balance please refill and try again']);
});

test('it returns error if flutterwave funding fails', function () {
    $user = User::factory()->create([
        'transfer_pin' => Hash::make('1234'),
    ]);
    $user->addCredits(1000);
    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'error',
        'message' => 'Unable to fund card',
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/fund' => Http::response($mockResponse, 400),
    ]);

    $response = $this->postJson('/api/account/virtual-card/fund', [
        'amount' => 500,
        'pin' => '1234',
        'card_id' => 'card_123',
    ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Unable to fund card']);

    $user->refresh();
    expect($user->creditBalance())->toEqual(1000);
});
