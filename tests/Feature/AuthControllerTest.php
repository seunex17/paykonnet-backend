<?php

use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('it can register a user successfully', function () {
    Mail::fake();

    $userData = [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];

    $response = postJson('/api/register', $userData);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user_data' => [
                'id',
                'name',
                'email',
                'uuid',
                'firstname',
                'lastname',
            ],
        ])
        ->assertJsonPath('user_data.email', $userData['email'])
        ->assertJsonPath('user_data.name', 'John Doe');

    assertDatabaseHas('users', [
        'email' => $userData['email'],
        'firstname' => 'John',
        'lastname' => 'Doe',
        'name' => 'John Doe',
    ]);

    $user = User::where('email', $userData['email'])->first();
    expect($user->uuid)->not->toBeNull()
        ->and(Str::isUuid($user->uuid))->toBeTrue();

    Mail::assertSent(VerifyEmailMail::class, function ($mail) use ($userData) {
        return $mail->hasTo($userData['email']);
    });
});

test('it fails registration if required fields are missing', function (string $field) {
    $userData = [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];

    unset($userData[$field]);

    $response = postJson('/api/register', $userData);

    $response->assertBadRequest();
})->with(['firstname', 'lastname', 'email', 'password']);

test('it fails registration with an invalid email', function () {
    $userData = [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
    ];

    $response = postJson('/api/register', $userData);

    $response->assertBadRequest();
});

test('it fails registration with a short password', function () {
    $userData = [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'short',
    ];

    $response = postJson('/api/register', $userData);

    $response->assertBadRequest();
});

test('it fails registration if email is already taken', function () {
    User::factory()->create(['email' => 'john.doe@example.com']);

    $userData = [
        'firstname' => 'Jane',
        'lastname' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];

    $response = postJson('/api/register', $userData);

    $response->assertBadRequest();
});
