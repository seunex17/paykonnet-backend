<?php

use App\Services\PaystackService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('paystack.secret_key', 'test-secret-key');
});

test('it verifies payment using laravel http', function () {
    Http::fake([
        'https://api.paystack.co/transaction/verify/REF123' => Http::response(['status' => true], 200),
    ]);

    $response = PaystackService::verifyPayment('REF123');

    expect(json_decode($response, true))->toBe(['status' => true]);

    Http::assertSent(fn ($request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paystack.co/transaction/verify/REF123');
});

test('it generates virtual account number using bearer token', function () {
    Http::fake([
        'https://api.paystack.co/dedicated_account' => Http::response(['status' => true], 200),
    ]);

    $response = PaystackService::generateVirtualAccountNumber([
        'customer' => 'CUS_test',
        'preferred_bank' => 'wema-bank',
    ]);

    expect(json_decode($response, true))->toBe(['status' => true]);

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && $request->hasHeader('Authorization', 'Bearer test-secret-key')
        && $request['customer'] === 'CUS_test'
        && $request['preferred_bank'] === 'wema-bank');
});

test('it lists all banks with nigeria country query', function () {
    Http::fake([
        'https://api.paystack.co/bank*' => Http::response(['status' => true, 'data' => []], 200),
    ]);

    $response = PaystackService::listAllBanks();

    expect($response)->toBe(['status' => true, 'data' => []]);

    Http::assertSent(fn ($request): bool => $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer test-secret-key')
        && $request->url() === 'https://api.paystack.co/bank?country=nigeria');
});

test('it resolves bank account using query data', function () {
    Http::fake([
        'https://api.paystack.co/bank/resolve*' => Http::response(['status' => true], 200),
    ]);

    $response = PaystackService::resolveBankAccount([
        'bank_code' => '044',
        'account_no' => '0123456789',
    ]);

    expect(json_decode($response, true))->toBe(['status' => true]);

    Http::assertSent(fn ($request): bool => $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer test-secret-key')
        && $request->url() === 'https://api.paystack.co/bank/resolve?bank_code=044&account_number=0123456789');
});

test('it creates transfer recipient and initiates transfer when recipient succeeds', function () {
    Http::fake([
        'https://api.paystack.co/transferrecipient' => Http::response([
            'status' => true,
            'data' => ['recipient_code' => 'RCP_test'],
        ], 200),
        'https://api.paystack.co/transfer' => Http::response([
            'status' => true,
            'data' => ['transfer_code' => 'TRF_test'],
        ], 200),
    ]);

    $response = PaystackService::createBankTransfer([
        'bank_code' => '044',
        'account_no' => '0123456789',
        'amount' => '2500',
        'narration' => 'Test transfer',
        'accountName' => 'Test User',
    ]);

    expect(json_decode($response, true))->toBe([
        'status' => true,
        'data' => ['transfer_code' => 'TRF_test'],
    ]);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.paystack.co/transferrecipient'
        && $request['bank_code'] === '044'
        && $request['account_number'] === '0123456789'
        && $request['type'] === 'nuban'
        && $request['currency'] === 'NGN');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.paystack.co/transfer'
        && $request['source'] === 'balance'
        && $request['amount'] === 250000
        && $request['recipient'] === 'RCP_test');
});

test('it returns transfer recipient response when recipient creation fails', function () {
    Http::fake([
        'https://api.paystack.co/transferrecipient' => Http::response([
            'status' => false,
            'message' => 'Invalid account',
        ], 422),
    ]);

    $response = PaystackService::createBankTransfer([
        'bank_code' => '044',
        'account_no' => '0123456789',
        'amount' => '2500',
        'narration' => 'Test transfer',
        'accountName' => 'Test User',
    ]);

    expect(json_decode($response, true))->toBe([
        'status' => false,
        'message' => 'Invalid account',
    ]);

    Http::assertSentCount(1);
});
