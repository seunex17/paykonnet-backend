<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    private const string BASE_URL = 'https://api.paystack.co/';

    public static function verifyPayment(string $ref): string
    {
        $response = Http::get(self::baseUrl("transaction/verify/$ref"));

        return $response->body();
    }

    public static function generateVirtualAccountNumber(array $data): string
    {
        $response = Http::withToken(config('paystack.secret_key'))
            ->post(self::baseUrl('dedicated_account'), $data);

        return $response->body();
    }

    public static function listAllBanks()
    {
        $response = Http::withToken(config('paystack.secret_key'))
            ->get(self::baseUrl('bank'), [
                'country' => 'nigeria',
            ]);

        return $response->json();
    }

    public static function resolveBankAccount(array $data)
    {
        $response = Http::withToken(config('paystack.secret_key'))
            ->get(self::baseUrl('bank/resolve'), [
                'bank_code' => $data['bank_code'],
                'account_number' => $data['account_no'],
            ]);

        return $response->json();
    }

    public static function createBankTransfer(array $data)
    {
        $response = Http::withToken(config('paystack.secret_key'))
            ->post(self::baseUrl('transferrecipient'), [
                'bank_code' => $data['bank_code'],
                'account_number' => $data['account_no'],
                'amount' => $data['amount'],
                'description' => $data['narration'],
                'type' => 'nuban',
                'name' => $data['accountName'],
                'currency' => 'NGN',
            ]);

        $result = json_decode($response->json());

        if (isset($result->status) && $result->status) {
            return self::initiateTransfer((string) $result->data->recipient_code, (string) $data['amount']);
        }

        return $response->json();
    }

    private static function initiateTransfer(string $code, string $amount): string
    {
        $response = Http::withToken(config('paystack.secret_key'))
            ->post(self::baseUrl('transfer'), [
                'source' => 'balance',
                'amount' => (int) $amount * 100,
                'recipient' => $code,
            ]);

        return $response->body();
    }

    public static function verifyTransaction(string $id)
    {
        $response = Http::withToken(config('paystack.secret_key'))
            ->get("https://api.paystack.co/transaction/verify/{$id}");

        return $response->json();
    }

    private static function baseUrl(string $param): string
    {
        return self::BASE_URL.$param;
    }
}
