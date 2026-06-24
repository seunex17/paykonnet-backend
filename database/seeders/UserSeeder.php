<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: UserSeeder.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/24/26
 * Time: 10:00 PM
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(database_path('seeders/data/users.json'));
        $data = json_decode($json, true);

        // Find the table entry in the JSON structure
        $tableData = collect($data)->firstWhere('type', 'table');

        if ($tableData && isset($tableData['data'])) {
            foreach ($tableData['data'] as $item) {
                DB::table('users')->updateOrInsert(
                    ['id' => $item['id']],
                    [
                        'uuid' => $item['uuid'] ?? null,
                        'firstname' => $item['firstname'] ?? null,
                        'lastname' => $item['lastname'] ?? null,
                        'name' => trim(($item['firstname'] ?? '').' '.($item['lastname'] ?? '')),
                        'email' => $item['email'] ?? null,
                        'password' => $item['password'] ?? null,
                        'lockscreen' => $item['lockscreen'] ?? null,
                        'transfer_pin' => $item['transfer_pin'] ?? null,
                        'id_type' => $item['id_type'] ?? null,
                        'id_number' => $item['id_number'] ?? null,
                        'id_verified' => ($item['id_verified'] ?? '0') === '1',
                        'agent_level' => $item['agent_level'] ?? '0',
                        'next_agent_payment_date' => $item['next_agent_payment_date'] ?? null,
                        'mono_id' => $item['mono_id'] ?? null,
                        'mono_mandate' => $item['mono_mandate'] ?? null,
                        'email_verified_at' => $item['email_verified_at'] ?? null,
                        'created_at' => $item['created_at'] ?? null,
                        'updated_at' => $item['updated_at'] ?? null,
                    ]
                );
            }
        }
    }
}
