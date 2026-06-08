<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it requires authentication', function () {
    $response = $this->getJson('/api/main/list-all-banks');

    $response->assertUnauthorized();
});

test('it lists all banks from paystack', function () {
    config()->set('paystack.secret_key', 'test-secret-key');
    Sanctum::actingAs(User::factory()->create());

    Http::fake([
        'https://api.paystack.co/bank*' => Http::response([
            'status' => true,
            'data' => [
                [
                    'name' => 'Access Bank',
                    'code' => '044',
                ],
                [
                    'name' => 'GTBank',
                    'code' => '058',
                ],
            ],
        ], 200),
    ]);

    $response = $this->getJson('/api/main/list-all-banks');

    $response->assertOk()
        ->assertJson([
            [
                'name' => 'Access Bank',
                'code' => '044',
            ],
            [
                'name' => 'GTBank',
                'code' => '058',
            ],
        ]);

    Http::assertSent(fn ($request): bool => $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer test-secret-key')
        && $request->url() === 'https://api.paystack.co/bank?country=nigeria');
});

test('it returns an error when paystack cannot retrieve banks', function () {
    Sanctum::actingAs(User::factory()->create());

    Http::fake([
        'https://api.paystack.co/bank*' => Http::response([
            'status' => false,
            'message' => 'Service unavailable',
        ], 200),
    ]);

    $response = $this->getJson('/api/main/list-all-banks');

    $response->assertUnprocessable()
        ->assertJson([
            'message' => 'Can not retrieve banks',
        ]);
});
