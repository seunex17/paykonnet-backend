<?php

use App\Mail\PasswordResetMail;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
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

    actingAs($user);

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

    actingAs($user);

    $response = postJson('/api/verify-email', [
        'email' => $user->email,
        'code' => '0000',
    ]);

    $response->assertBadRequest();
});

test('it fails verification if user email not found', function () {
    actingAs(User::factory()->create());

    $response = postJson('/api/verify-email', [
        'email' => 'nonexistent@example.com',
        'code' => '1234',
    ]);

    $response->assertNotFound();
});

test('it fails verification with validation error', function () {
    actingAs(User::factory()->create());

    $response = postJson('/api/verify-email', [
        'email' => 'test3@example.com',
        // 'code' is missing
    ]);

    $response->assertBadRequest();
});

test('it can resend email verification code', function () {
    Mail::fake();

    $user = User::factory()->create();

    actingAs($user);

    $response = postJson('/api/resend-email-verify-code', [
        'email' => $user->email,
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'New code has been send to your email')
        ->assertJsonPath('user.email', $user->email);

    Mail::assertSent(VerifyEmailMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('it cannot resend verification code if unauthenticated', function () {
    $response = postJson('/api/resend-email-verify-code', [
        'email' => 'test@example.com',
    ]);

    $response->assertUnauthorized();
});

test('it returns not found if email does not exist during resend', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = postJson('/api/resend-email-verify-code', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertNotFound()
        ->assertJsonPath('message', 'User not found');
});

test('it can set lockscreen pin successfully', function () {
    $user = User::factory()->create();

    actingAs($user);

    $pin = '1234';

    $response = postJson('/api/lockscreen', [
        'pin' => $pin,
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Login pin set successfully')
        ->assertJsonPath('user_data.email', $user->email);

    $user->refresh();
    expect(Hash::check($pin, $user->lockscreen))->toBeTrue();
});

test('it cannot set lockscreen pin if unauthenticated', function () {
    $response = postJson('/api/lockscreen', [
        'pin' => '1234',
    ]);

    $response->assertUnauthorized();
});

test('it can verify lockscreen pin successfully', function () {
    $pin = '1234';
    $user = User::factory()->create([
        'lockscreen' => Hash::make($pin),
    ]);

    actingAs($user);

    $response = postJson('/api/verify-lockscreen', [
        'pin' => $pin,
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Success');
});

test('it fails verification with invalid lockscreen pin', function () {
    $user = User::factory()->create([
        'lockscreen' => Hash::make('1234'),
    ]);

    actingAs($user);

    $response = postJson('/api/verify-lockscreen', [
        'pin' => '5678',
    ]);

    $response->assertBadRequest()
        ->assertJsonPath('message', 'Invalid login pin');
});

test('it cannot verify lockscreen pin if unauthenticated', function () {
    $response = postJson('/api/verify-lockscreen', [
        'pin' => '1234',
    ]);

    $response->assertUnauthorized();
});

test('it can login successfully', function () {
    $password = 'password123';
    $user = User::factory()->create([
        'password' => Hash::make($password),
        'is_active' => true,
    ]);

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'user_data' => [
                'id',
                'name',
                'email',
            ],
            'token',
        ])
        ->assertJsonPath('user_data.email', $user->email);
});

test('it fails login with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertBadRequest()
        ->assertJsonPath('message', 'Invalid email or password');
});

test('it fails login if account is inactive', function () {
    $password = 'password123';
    $user = User::factory()->create([
        'password' => Hash::make($password),
        'is_active' => false,
    ]);

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertBadRequest()
        ->assertJsonPath('message', 'Your account is not active');
});

test('it fails login with validation errors', function (array $data) {
    $response = postJson('/api/login', $data);

    $response->assertBadRequest();
})->with([
    'missing email' => [['password' => 'password123']],
    'missing password' => [['email' => 'test@example.com']],
    'invalid email' => [['email' => 'not-an-email', 'password' => 'password123']],
]);

test('it can set transfer pin successfully', function () {
    $user = User::factory()->create();

    actingAs($user);

    $pin = '1234';

    $response = postJson('/api/transfer-pin', [
        'pin' => $pin,
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Transfer pin set successfully')
        ->assertJsonPath('user_data.email', $user->email);

    $user->refresh();
    expect(Hash::check($pin, $user->transfer_pin))->toBeTrue();
});

test('it cannot set transfer pin if unauthenticated', function () {
    $response = postJson('/api/transfer-pin', [
        'pin' => '1234',
    ]);

    $response->assertUnauthorized();
});

test('it can request a new password successfully', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'reset@example.com',
        'password' => Hash::make('old-password'),
    ]);

    actingAs($user);

    $response = postJson('/api/forget-password', [
        'email' => $user->email,
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'New password sent to your email');

    $user->refresh();
    expect(Hash::check('old-password', $user->password))->toBeFalse();

    Mail::assertSent(PasswordResetMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('it returns success even if user not found for security', function () {
    Mail::fake();

    $user = User::factory()->create();
    actingAs($user);

    $response = postJson('/api/forget-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'New password sent to your email');

    Mail::assertNothingSent();
});

test('it can request new password if unauthenticated', function () {
    $response = postJson('/api/forget-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertOk();
});

test('it can get logged user data successfully', function () {
    $user = User::factory()->create();

    actingAs($user);

    $response = postJson('/api/logged-user');

    $response->assertOk()
        ->assertJsonPath('email', $user->email)
        ->assertJsonPath('id', $user->id);
});

test('it cannot get logged user data if unauthenticated', function () {
    $response = postJson('/api/logged-user');

    $response->assertUnauthorized();
});
