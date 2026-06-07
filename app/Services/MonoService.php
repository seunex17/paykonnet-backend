<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: MonoService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/7/26
 * Time: 10:48 AM
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MonoService
{
    private const string BASE_URI = 'https://api.withmono.com/';

    public static function authenticate(string $code)
    {
        $request = Http::withToken(config('mono.secret_key'))
            ->post(self::BASE_URI.'v2/accounts/auth', [
                'code' => $code,
            ]);

        return $request->json();
    }

    public static function createMandate(array $data)
    {
        $request = Http::withToken(config('mono.secret_key'))
            ->post(self::BASE_URI.'v2/payments/initiate', $data);

        return $request->json();
    }
}
