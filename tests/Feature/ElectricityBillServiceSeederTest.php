<?php

use App\Models\ElectricBillService;
use Database\Seeders\ElectricityBillServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('electricity bill service seeder seeds data correctly from json', function () {
    // Run the seeder
    $this->seed(ElectricityBillServiceSeeder::class);

    // Assert that the data was seeded
    // The JSON has 10 items
    expect(ElectricBillService::count())->toBe(10);

    // Verify specific record from JSON
    // {"id":"1","name":"Ikeja Electricity Distribution Company (IKEDC)","code":"ikeja-electric",...}
    $this->assertDatabaseHas('electric_bill_services', [
        'id' => 1,
        'name' => 'Ikeja Electricity Distribution Company (IKEDC)',
        'code' => 'ikeja-electric',
    ]);

    // Verify another record
    // {"id":"10","name":"Benin Electricity Distribution Company (BEDC)","code":"benin-electric",...}
    $this->assertDatabaseHas('electric_bill_services', [
        'id' => 10,
        'name' => 'Benin Electricity Distribution Company (BEDC)',
        'code' => 'benin-electric',
    ]);
});
