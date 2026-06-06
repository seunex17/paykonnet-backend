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
}
