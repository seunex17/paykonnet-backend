<?php

use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
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

test('it can verify email successfully', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'is_active' => false,
        'email_verified_at' => null,
    ]);

    $otp = (new Otp)->generate($user->email, 'numeric', 4, 120);

    $response = postJson('/api/verify-email', [
        'email' => $user->email,
        'code' => $otp->token,
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Verification Successful')
        ->assertJsonPath('user.email', $user->email);

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->is_active)->toBeTrue();

    Mail::assertQueued(WelcomeMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('it fails verification with invalid code', function () {
    $user = User::factory()->create([
        'email' => 'test2@example.com',
    ]);

    $response = postJson('/api/verify-email', [
        'email' => $user->email,
        'code' => '0000',
    ]);

    $response->assertBadRequest();
});

test('it fails verification if user email not found', function () {
    $response = postJson('/api/verify-email', [
        'email' => 'nonexistent@example.com',
        'code' => '1234',
    ]);

    $response->assertNotFound();
});

test('it fails verification with validation error', function () {
    $response = postJson('/api/verify-email', [
        'email' => 'test3@example.com',
        // 'code' is missing
    ]);

    $response->assertBadRequest();
});
