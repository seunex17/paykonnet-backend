<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: FlutterwaveService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/6/26
 * Time: 12:37 PM
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FlutterwaveService
{
    private const string BASE_URL = 'https://api.flutterwave.com/v3/';

    public static function fetchVirtualCard(string $id): ?array
    {
        $response = Http::withToken(config('flutterwave.secret_key'))
            ->get(self::BASE_URL."virtual-cards/$id");

        return $response->json();
    }

    public static function virtualCardTransactions(string $id): ?array
    {
        $response = Http::withToken(config('flutterwave.secret_key'))
            ->get(self::BASE_URL."virtual-cards/$id/transactions", [
                'from' => date('Y').'-01-01',
                'to' => date('Y').'-12-01',
                'index' => 0,
                'size' => 50,
            ]);

        return $response->json();
    }

    public static function createVirtualCard(array $data)
    {
        $response = Http::withToken(config('flutterwave.secret_key'))
            ->post(self::BASE_URL.'virtual-cards', [
                'currency' => $data['currency'],
                'amount' => $data['amount'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'title' => $data['title'],
                'gender' => $data['gender'],
            ]);

        return $response->json();
    }

    public static function blockVirtualCard(string $id)
    {
        $response = Http::withToken(config('flutterwave.secret_key'))
            ->put(self::BASE_URL."virtual-cards/$id/status/block");

        return $response->json();
    }

    public static function unBlockVirtualCard(string $id)
    {
        $response = Http::withToken(config('flutterwave.secret_key'))
            ->put(self::BASE_URL."virtual-cards/$id/status/unblock");

        return $response->json();
    }

    public static function fundVirtualCard(array $data, string $id)
    {
        $response = Http::withToken(config('flutterwave.secret_key'))
            ->post(self::BASE_URL."virtual-cards/$id/fund", [
                'debit_currency' => $data['currency'],
                'amount' => $data['amount'],
            ]);

        return $response->json();
    }
}
