<?php

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('wallet can be created with required fields', function () {
    $user = User::factory()->create();

    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'account_number' => '1234567890',
        'bank_name' => 'Test Bank',
        'references' => ['ref1' => 'val1'],
    ]);

    expect($wallet->user_id)->toBe($user->id)
        ->and($wallet->account_number)->toBe('1234567890')
        ->and($wallet->bank_name)->toBe('Test Bank')
        ->and($wallet->references)->toBe(['ref1' => 'val1']);
});

test('wallet belongs to a user', function () {
    $wallet = Wallet::factory()->create();

    expect($wallet->user)->toBeInstanceOf(User::class);
});

test('user has many wallets', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);

    expect($user->wallets)->toHaveCount(1)
        ->and($user->wallets->first()->id)->toBe($wallet->id);
});
