<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: DataLoanListSeeder.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/24/26
 * Time: 5:43 PM
 */

namespace Database\Seeders;

use App\Models\DataLoanList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DataLoanListSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('seeders/data/data_loan_lists.json'));
        $data = json_decode($json, true);

        // Find the table entry in the JSON structure
        $tableData = collect($data)->firstWhere('type', 'table');

        if ($tableData && isset($tableData['data'])) {
            foreach ($tableData['data'] as $item) {
                DataLoanList::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'service_id' => $item['service_id'],
                        'name' => $item['name'],
                        'code' => $item['code'],
                        'price' => $item['price'],
                    ]
                );
            }
        }
    }
}
