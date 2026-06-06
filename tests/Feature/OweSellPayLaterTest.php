<?php

use App\Models\SellPayLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can retrieve the total amount owed for sell pay later', function () {
    $user = User::factory()->create();

    SellPayLater::factory()->create([
        'user_id' => $user->id,
        'amount' => 100.50,
        'paid' => false,
    ]);

    SellPayLater::factory()->create([
        'user_id' => $user->id,
        'amount' => 200.75,
        'paid' => false,
    ]);

    // This should be excluded as it is paid
    SellPayLater::factory()->create([
        'user_id' => $user->id,
        'amount' => 500.00,
        'paid' => true,
    ]);

    // This should be excluded as it belongs to another user
    SellPayLater::factory()->create([
        'user_id' => User::factory()->create()->id,
        'amount' => 1000.00,
        'paid' => false,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/owe-sell-pay-later');

    $response->assertOk()
        ->assertJson([
            'amount' => 301.25,
        ]);
});

test('it returns 0 if there are no unpaid sell pay later records', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/owe-sell-pay-later');

    $response->assertOk()
        ->assertJson([
            'amount' => 0,
        ]);
});

test('it returns unauthorized for guest users', function () {
    $response = $this->getJson('/api/account/owe-sell-pay-later');

    $response->assertUnauthorized();
});
