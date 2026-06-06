<?php

use App\Models\SellPayLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can retrieve the history of unpaid sell pay later records', function () {
    $user = User::factory()->create();

    $unpaidRecords = SellPayLater::factory()->count(3)->create([
        'user_id' => $user->id,
        'paid' => false,
    ])->sortByDesc('created_at')->values();

    // These should be excluded
    SellPayLater::factory()->create([
        'user_id' => $user->id,
        'paid' => true,
    ]);

    SellPayLater::factory()->create([
        'user_id' => User::factory()->create()->id,
        'paid' => false,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/owe-spl-history');

    $response->assertOk()
        ->assertJsonCount(3);

    // Verify the data returned matches the unpaid records
    foreach ($unpaidRecords as $index => $record) {
        $response->assertJsonPath("$index.id", $record->id);
        $response->assertJsonPath("$index.product", $record->product);
        $response->assertJsonPath("$index.amount", (string) $record->amount);
        $response->assertJsonPath("$index.paid", false);
    }
});

test('it returns an empty array if there are no unpaid sell pay later records', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/account/owe-spl-history');

    $response->assertOk()
        ->assertExactJson([]);
});

test('it returns unauthorized for guest users', function () {
    $response = $this->getJson('/api/account/owe-spl-history');

    $response->assertUnauthorized();
});
