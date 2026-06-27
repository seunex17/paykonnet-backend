<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: FidelityService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/6/26
 * Time: 6:42 PM
 */

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class FidelityService
{
    private const string BASE_URL = 'https://api.paygateplus.ng/v2/';

    public static function openVirtualAccount(array $data, User $user)
    {
        $request_ref = mt_rand(100000000, 999999999);
        $response = Http::withToken(config('fidelity.api_key'))
            ->withHeaders([
                'Signature' => md5($request_ref.';'.config('fidelity.secret_key')),
            ])
            ->post(self::BASE_URL.'transact', [
                'request_ref' => $request_ref,
                'request_type' => 'open_account',
                'auth' => [
                    'type' => null,
                    'secure' => null,
                    'auth_provider' => 'FidelityVirtual',
                    'route_mode' => null,
                ],
                'transaction' => [
                    'mock_mode' => 'live',
                    'transaction_ref' => time(),
                    'transaction_desc' => 'Open account',
                    'amount' => 0,
                    'customer' => [
                        'customer_ref' => $data['phone_no'],
                        'firstname' => $user->firstname,
                        'surname' => $user->firstname,
                        'email' => $user->email,
                        'mobile_no' => $data['phone_no'],
                    ],
                    'meta' => [
                        'user_id' => $user->id,
                    ],
                    'details' => [
                        'otp_override' => true,
                        'name_on_account' => $user->firstname.' '.$user->lastname,
                        'middlename' => '',
                        'dob' => $data['date_of_birth'],
                        'gender' => $data['gender'],
                        'title' => $data['title'],
                        'address_line_1' => $data['residential_address'],
                        'address_line_2' => 'nill',
                        'city' => $data['state_of_residence'],
                        'state' => $data['state_of_residence'],
                        'country' => 'Nigeria',
                    ],
                ],
            ]);

        return $response->json();
    }
}
