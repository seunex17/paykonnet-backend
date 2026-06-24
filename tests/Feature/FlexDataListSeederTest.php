<?php

use App\Models\FlexDataList;
use Database\Seeders\FlexDataListSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('flex data list seeder seeds data correctly from json', function () {
    // Run the seeder
    $this->seed(FlexDataListSeeder::class);

    // Assert that the data was seeded
    // The JSON has many items. Let's count them or check a few.
    // Based on the JSON content, it has items from id 1 up to 118 (with some gaps maybe, but id 118 is there).
    expect(FlexDataList::count())->toBeGreaterThan(0);

    // Verify first record from JSON
    // {"id":"1","status":"1","service_id":"1","name":"500 MB (SME)","code":"500.0","price":"212.00","duration":"30",...}
    $this->assertDatabaseHas('flex_data_lists', [
        'id' => 1,
        'service_id' => '1',
        'name' => '500 MB (SME)',
        'code' => '500.0',
        'price' => 212.00,
        'duration' => '30',
        'status' => 'active',
    ]);

    // Verify another record
    // {"id":"118","status":"1","service_id":"3","name":"190GB - 180 days (Direct Data)","code":"150000.01","price":"123500.00","duration":"180",...}
    $this->assertDatabaseHas('flex_data_lists', [
        'id' => 118,
        'service_id' => '3',
        'name' => '190GB - 180 days (Direct Data)',
        'code' => '150000.01',
        'price' => 123500.00,
        'duration' => '180',
        'status' => 'active',
    ]);
});
