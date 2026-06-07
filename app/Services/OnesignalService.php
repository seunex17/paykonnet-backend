<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: OnesignalService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/7/26
 * Time: 7:47 AM
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OnesignalService
{
    public static function sendPushNotification(array $data)
    {
        $response = Http::withToken(config('onesignal.secret'))
            ->post('https://onesignal.com/api/v1/notifications', [
                'included_segments' => 'Total Subscriptions',
                'contents' => [
                    'en' => $data['contents'],
                ],
                'headings' => [
                    'en' => $data['title'],
                ],
                'app_id' => config('onesignal.app_id'),
                'large_icon' => $data['icon'] ?? null,
                'ios_attachments' => $data['icon'] ?? null,
                'filters' => $data['filters'] ?? null,
            ]);

        return $response->json();
    }
}
