<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: UtilityService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/6/26
 * Time: 8:33 AM
 */

namespace App\Services;

use App\Models\User;
use Random\RandomException;

class UtilityService
{
    public function __construct() {}

    /**
     * @throws RandomException
     */
    public static function generateStrongPassword(int $length = 16): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        if ($length < 8) {
            $length = 8;
        }

        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];

        $allCharacters = $lowercase.$uppercase.$numbers.$special;
        $allLength = strlen($allCharacters);

        while (count($password) < $length) {
            $password[] = $allCharacters[random_int(0, $allLength - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }

    public static function agentPercent(User $user, float $cost): float
    {
        $agentLevel = $user->agent_level;

        return match ((int) $agentLevel) {
            1 => ($cost / 100) * 5,
            2 => ($cost / 100) * 2.5,
            default => ($cost / 100) * 1.5,
        };
    }
}
