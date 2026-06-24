<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: ElectricityBillServiceSeeder.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/24/26
 * Time: 9:44 PM
 */

namespace Database\Seeders;

use App\Models\ElectricBillService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ElectricityBillServiceSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('seeders/data/electric_bill_services.json'));
        $data = json_decode($json, true);

        // Find the table entry in the JSON structure
        $tableData = collect($data)->firstWhere('type', 'table');

        if ($tableData && isset($tableData['data'])) {
            foreach ($tableData['data'] as $item) {
                ElectricBillService::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'name' => $item['name'],
                        'code' => $item['code'],
                    ]
                );
            }
        }
    }
}
