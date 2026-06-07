<?php

use App\Models\User;
use App\Models\VirtualCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can block a virtual card successfully', function () {
    $user = User::factory()->create();
    $card = VirtualCard::factory()->create([
        'user_id' => $user->id,
        'card_id' => 'card_123',
        'is_block' => false,
    ]);

    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'success',
        'message' => 'Card blocked successfully',
        'data' => null,
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/status/block' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson("/api/account/virtual-card/block/{$card->card_id}");

    $response->assertOk()
        ->assertJson(['message' => 'Card blocked successfully']);

    $card->refresh();
    expect($card->is_block)->toBeTrue();
});

test('it returns error if flutterwave block fails', function () {
    $user = User::factory()->create();
    $card = VirtualCard::factory()->create([
        'user_id' => $user->id,
        'card_id' => 'card_123',
        'is_block' => false,
    ]);

    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'error',
        'message' => 'Unable to block card',
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/status/block' => Http::response($mockResponse, 400),
    ]);

    $response = $this->postJson("/api/account/virtual-card/block/{$card->card_id}");

    $response->assertStatus(404)
        ->assertJson(['message' => 'Unable to block card']);

    $card->refresh();
    expect($card->is_block)->toBeFalse();
});

test('it can unblock a virtual card successfully', function () {
    $user = User::factory()->create();
    $card = VirtualCard::factory()->create([
        'user_id' => $user->id,
        'card_id' => 'card_123',
        'is_block' => true,
    ]);

    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'success',
        'message' => 'Card unblocked successfully',
        'data' => null,
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/status/unblock' => Http::response($mockResponse, 200),
    ]);

    $response = $this->postJson("/api/account/virtual-card/unblock/{$card->card_id}");

    $response->assertOk()
        ->assertJson(['message' => 'Card unblocked successfully']);

    $card->refresh();
    expect($card->is_block)->toBeFalse();
});

test('it returns error if flutterwave unblock fails', function () {
    $user = User::factory()->create();
    $card = VirtualCard::factory()->create([
        'user_id' => $user->id,
        'card_id' => 'card_123',
        'is_block' => true,
    ]);

    Sanctum::actingAs($user);

    $mockResponse = [
        'status' => 'error',
        'message' => 'Unable to unblock card',
    ];

    Http::fake([
        'api.flutterwave.com/v3/virtual-cards/*/status/unblock' => Http::response($mockResponse, 400),
    ]);

    $response = $this->postJson("/api/account/virtual-card/unblock/{$card->card_id}");

    $response->assertStatus(404)
        ->assertJson(['message' => 'Unable to unblock card']);

    $card->refresh();
    expect($card->is_block)->toBeTrue();
});
