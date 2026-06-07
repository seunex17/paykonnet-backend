<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: QoreIdService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/7/26
 * Time: 8:00 AM
 */

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class QoreIdService
{
    private const string BASE_URL = 'https://api.qoreid.com/';

    public static function identification(User $user, string $type, string $number)
    {
        $token = self::accessToken();

        $method = match ($type) {
            'nin' => 'nin',
            'vc' => 'vin',
            default => 'drivers-license',
        };

        $request = Http::withToken($token)
            ->post(self::BASE_URL."v1/ng/identities/$method/$number", [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
            ]);

        return $request->json();
    }

    public static function accessToken()
    {
        $request = Http::post(self::BASE_URL.'token', [
            'client_id' => config('qoreid.client_id'),
            'secret' => config('qoreid.secret'),
        ]);

        return $request->json()['accessToken'];
    }
}
