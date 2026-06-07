<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can create a virtual card successfully', function () {
    $user = User::factory()->create([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'john@example.com',
    ]);

    // Give user enough credits (150 required)
    $user->addCredits(200);

    Sanctum::actingAs($user);

    $payload = [
        'dob' => '1990-01-01',
        'phone' => '08012345678',
        'title' => 'Male',
    ];

    $mockResponse = [
        'status' => 'success',
        'message' => 'Virtual card created successfully',
        'data' => [
            'id' => 'card_123',
            'account_id' => 'acc_123',
            'currency' => 'NGN',
            'card_pan' => '412345XXXXXX1234',
            'masked_pan' => '412345******1234',
            'city' => 'Lagos',
            'state' => 'Lagos',
            'address_1' => '123 Test St',
            'cvv' => '123',
            'expiration' => '01/25',
            'card_type' => 'VISA',
            'name_on_card' => 'John Doe',
        ],
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson('/api/account/create-virtual-card', $payload);

    $response->assertOk()
        ->assertJson(['message' => 'Card created successfully']);

    // Check VirtualCard record
    $this->assertDatabaseHas('virtual_cards', [
        'user_id' => $user->id,
        'card_id' => 'card_123',
        'card_pan' => '412345XXXXXX1234',
    ]);

    // Check Transaction record
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'type' => 'debit',
        'amount' => 150,
        'note' => 'Virtual card insurance fee',
        'source_table' => 'virtual_cards',
    ]);

    // Check credits deduction
    $user->refresh();
    expect($user->creditBalance())->toEqual(50); // 200 - 150
});

test('it returns error if user has insufficient credits', function () {
    $user = User::factory()->create();
    $user->addCredits(100); // Less than 150

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/account/create-virtual-card', [
        'dob' => '1990-01-01',
        'phone' => '08012345678',
        'title' => 'Male',
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Insufficient account balance please refill and try again']);
});

test('it returns error if flutterwave creation fails', function () {
    $user = User::factory()->create();
    $user->addCredits(200);

    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'error',
        'message' => 'Unable to create card',
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards' => Http::response($mockResponse, 400),
    ]);

    $response = $this->postJson('/api/account/create-virtual-card', [
        'dob' => '1990-01-01',
        'phone' => '08012345678',
        'title' => 'Male',
    ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Unable to create card']);

    // Ensure no credits were deducted and no card/transaction created
    $user->refresh();
    expect($user->creditBalance())->toEqual(200);
    $this->assertDatabaseEmpty('virtual_cards');
    $this->assertDatabaseEmpty('transactions');
});

test('it handles gender based on title', function () {
    $user = User::factory()->create();
    $user->addCredits(400);
    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'success',
        'message' => 'Created',
        'data' => [
            'id' => 'card_124',
            'account_id' => 'acc_124',
            'currency' => 'NGN',
            'card_pan' => '412345XXXXXX1235',
            'masked_pan' => '412345******1235',
            'city' => 'Lagos',
            'state' => 'Lagos',
            'address_1' => '123 Test St',
            'cvv' => '123',
            'expiration' => '01/25',
            'card_type' => 'VISA',
            'name_on_card' => 'Jane Doe',
        ],
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards' => Http::response($mockResponse, 200),
    ]);

    // Test with 'Female' title
    $this->postJson('/api/account/create-virtual-card', [
        'dob' => '1990-01-01',
        'phone' => '08012345678',
        'title' => 'Mrs', // Not 'Male'
    ]);

    Http::assertSent(function ($request) {
        return $request['gender'] === 'F';
    });

    // Test with 'Male' title
    $this->postJson('/api/account/create-virtual-card', [
        'dob' => '1990-01-01',
        'phone' => '08012345678',
        'title' => 'Male',
    ]);

    Http::assertSent(function ($request) {
        return $request['gender'] === 'M';
    });
});
