<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: ClubConnectService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/8/26
 * Time: 12:16 PM
 */

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ClubConnectService
{
    protected static string $baseUrl = 'https://www.nellobytesystems.com';

    protected static function http(): PendingRequest|Factory
    {
        return Http::timeout(30)
            ->acceptJson();
    }

    /**
     * @throws ConnectionException
     */
    protected static function get(string $endpoint, array $query = []): array
    {
        $response = static::http()->get(static::$baseUrl.$endpoint, $query);

        return static::parseResponse($response);
    }

    protected static function parseResponse(Response $response): array
    {
        if ($response->failed()) {
            return [
                'error' => true,
                'status' => $response->status(),
                'message' => 'HTTP request failed.',
            ];
        }

        $decoded = $response->json();

        if (is_null($decoded)) {
            return [
                'error' => false,
                'raw' => $response->body(),
            ];
        }

        return $decoded;
    }

    public static function listMobileDataPlans(): array
    {
        return static::get('/APIDatabundlePlansV2.asp', [
            'UserID' => config('clubconnect.UserID'),
        ]);
    }

    public static function purchaseMobileDataPlans(array $data): array
    {
        $productId = $data['network'] ?? config('smeplug.CKProductArray.'.$data['product']);

        return static::get('/APIDatabundleV1.asp', [
            'UserID' => config('clubconnect.user_id'),
            'APIKey' => config('clubconnect.api_key'),
            'MobileNetwork' => $productId,
            'DataPlan' => $data['code'],
            'MobileNumber' => $data['phone_no'],
            'RequestID' => $data['references'],
            'CallBackURL' => url('vending-webhook/mobile-data'),
        ]);
    }

    public static function purchaseMobileAirtime(array $data): array
    {
        $productId = config('smeplug.CKProductArray.'.$data['product']);

        return static::get('/APIAirtimeV1.asp', [
            'UserID' => config('clubconnect.user_id'),
            'APIKey' => config('clubconnect.api_key'),
            'MobileNetwork' => $productId,
            'Amount' => $data['amount'],
            'MobileNumber' => $data['phone_no'],
            'RequestID' => $data['references'],
            'CallBackURL' => url('vending-webhook/mobile-data'),
        ]);
    }

    public static function loadBettingCompanies(): array
    {
        return static::get('/APIBettingCompaniesV2.asp');
    }

    public static function verifyBettingCustomerId(array $data): array
    {
        return static::get('/APIVerifyBettingV1.asp', [
            'UserID' => config('clubconnect.user_id'),
            'APIKey' => config('clubconnect.api_key'),
            'BettingCompany' => $data['code'],
            'CustomerID' => $data['customer_id'],
        ]);
    }

    public static function fundBettingWallet(array $data): array
    {
        return static::get('/APIBettingV1.asp', [
            'UserID' => config('clubconnect.user_id'),
            'APIKey' => config('clubconnect.api_key'),
            'BettingCompany' => $data['code'],
            'CustomerID' => $data['customer_id'],
            'Amount' => $data['amount'],
        ]);
    }
}
