<?php

use App\Models\DataPlan;
use Database\Seeders\DataPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('data plan seeder seeds data correctly from json', function () {
    // Run the seeder
    $this->seed(DataPlanSeeder::class);

    // Assert that the data was seeded
    // The JSON has 42 items (id 1 to 42)
    expect(DataPlan::count())->toBe(42);

    // Verify specific record from JSON
    // {"id":"1","service_id":"1","service_name":"mtn","name":"MTN SME Data 500MB \u2013 30 Days For N199","code":"500","price":"199.00",...}
    $this->assertDatabaseHas('data_plans', [
        'id' => 1,
        'service_id' => '1',
        'service_name' => 'mtn',
        'name' => 'MTN SME Data 500MB – 30 Days For N199',
        'code' => '500',
        'price' => 199.00,
    ]);

    // Verify another record
    // {"id":"42","service_id":"3","service_name":"etisalat","name":"9mobile Data 15GB \u2013 30 Days For N9899","code":"9MOB5000","price":"9899.00",...}
    $this->assertDatabaseHas('data_plans', [
        'id' => 42,
        'service_id' => '3',
        'service_name' => 'etisalat',
        'name' => '9mobile Data 15GB – 30 Days For N9899',
        'code' => '9MOB5000',
        'price' => 9899.00,
    ]);
});
