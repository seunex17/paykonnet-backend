<?php

use App\Models\SellPayLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it has fillable attributes', function () {
    $user = User::factory()->create();

    $sellPayLater = SellPayLater::create([
        'user_id' => $user->id,
        'product' => 'Test Product',
        'amount' => 100.50,
        'paid' => false,
    ]);

    expect($sellPayLater->user_id)->toBe($user->id)
        ->and($sellPayLater->product)->toBe('Test Product')
        ->and($sellPayLater->amount)->toBe('100.50')
        ->and($sellPayLater->paid)->toBeFalse();
});

test('it belongs to a user', function () {
    $sellPayLater = SellPayLater::factory()->create();

    expect($sellPayLater->user)->toBeInstanceOf(User::class);
});

test('amount is cast to decimal and paid is cast to boolean', function () {
    $sellPayLater = SellPayLater::factory()->create([
        'amount' => '150.75',
        'paid' => 1,
    ]);

    expect($sellPayLater->amount)->toBe('150.75')
        ->and($sellPayLater->paid)->toBeTrue();
});
