<?php

namespace Database\Seeders;

use App\Models\FlexDataList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FlexDataListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(database_path('seeders/data/flex_data_lists.json'));
        $data = json_decode($json, true);

        // Find the table entry in the JSON structure
        $tableData = collect($data)->firstWhere('type', 'table');

        if ($tableData && isset($tableData['data'])) {
            foreach ($tableData['data'] as $item) {
                FlexDataList::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'service_id' => $item['service_id'] ?? null,
                        'name' => $item['name'] ?? null,
                        'code' => $item['code'] ?? null,
                        'price' => $item['price'] ?? 0,
                        'duration' => $item['duration'] ?? null,
                        'status' => ($item['status'] == '1') ? 'active' : 'inactive',
                    ]
                );
            }
        }
    }
}
