<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: DataPlanSeeder.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/24/26
 * Time: 5:48 PM
 */

namespace Database\Seeders;

use App\Models\DataPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DataPlanSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('seeders/data/data_plans.json'));
        $data = json_decode($json, true);

        // Find the table entry in the JSON structure
        $tableData = collect($data)->firstWhere('type', 'table');

        if ($tableData && isset($tableData['data'])) {
            foreach ($tableData['data'] as $item) {
                DataPlan::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'service_id' => $item['service_id'],
                        'service_name' => $item['service_name'],
                        'name' => $item['name'],
                        'code' => $item['code'],
                        'price' => $item['price'],
                    ]
                );
            }
        }
    }
}
