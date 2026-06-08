<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: VtpassService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/8/26
 * Time: 1:22 PM
 */

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class VTPassService
{
    protected static string $baseUrl = 'https://api-service.vtpass.com/api';

    protected static function url(string $path): string
    {
        return static::$baseUrl.'/'.ltrim($path, '/');
    }

    protected static function http(): PendingRequest
    {
        return Http::timeout(30)
            ->withHeaders([
                'api-key' => config('vtpass.api_key'),
                'secret-key' => config('vtpass.secret_key'),
                'public-key' => config('vtpass.public_key'),
            ]);
    }

    protected static function get(string $path, array $query = []): array
    {
        $response = static::http()->get(static::url($path), $query);

        return static::parseResponse($response);
    }

    protected static function post(string $path, array $form = []): array
    {
        $response = static::http()->asForm()->post(static::url($path), $form);

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

    public static function purchaseAirtime(array $request): array
    {
        return static::post('pay', $request);
    }

    public static function getDataVariationCode(string $service): array
    {
        return static::get('service-variations', ['serviceID' => $service]);
    }

    public static function getCableTvVariationCode(array $data): array
    {
        return static::get('service-variations', [
            'serviceID' => config('sme_plug.cable_products_array.'.$data['product']),
        ]);
    }

    public static function verifySmartCardNumber(array $data): array
    {
        return static::post('merchant-verify', [
            'billersCode' => $data['smart_card_no'],
            'serviceID' => config('sme_plug.cable_products_array.'.$data['product']),
        ]);
    }

    public static function purchaseCableBill(array $data): array
    {
        return static::post('pay', [
            'request_id' => $data['references'],
            'serviceID' => config('sme_plug.cable_products_array.'.$data['product']),
            'billersCode' => $data['smart_card_no'],
            'variation_code' => $data['code'],
            'amount' => $data['price'],
            'phone' => $data['phone_no'],
            'subscription_type' => 'renew',
        ]);
    }

    public static function verifyElectricityMeterNumber(array $request): array
    {
        return static::post('merchant-verify', $request);
    }

    public static function purchaseElectricity(array $request): array
    {
        return static::post('pay', $request);
    }

    public static function getJambVariationCode(): array
    {
        return static::get('service-variations', ['serviceID' => 'jamb']);
    }

    public static function verifyJambProfileId(array $data): array
    {
        return static::post('merchant-verify', [
            'billersCode' => $data['smart_card_no'],
            'serviceID' => 'jamb',
            'type' => $data['type'],
        ]);
    }

    public static function purchaseJambPin(array $data): array
    {
        return static::post('pay', [
            'request_id' => $data['references'],
            'serviceID' => 'jamb',
            'billersCode' => $data['smart_card_no'],
            'variation_code' => $data['type'],
            'amount' => $data['price'],
            'phone' => $data['phone_no'],
        ]);
    }
}
