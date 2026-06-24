<?php

use App\Models\DataLoanList;
use Database\Seeders\DataLoanListSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('data loan list seeder seeds data correctly from json', function () {
    // Run the seeder
    $this->seed(DataLoanListSeeder::class);

    // Assert that the data was seeded
    expect(DataLoanList::count())->toBe(3);

    // Verify specific record from JSON
    // {"id":"2","service_id":"1","name":"2GB Monthly for N1000","code":"M2024","price":"1000"}
    $this->assertDatabaseHas('data_loan_lists', [
        'id' => 2,
        'service_id' => 1,
        'name' => '2GB Monthly for N1000',
        'code' => 'M2024',
        'price' => 1000.00,
    ]);

    // {"id":"3","service_id":"1","name":"5GB Monthly for N2500","code":"5000","price":"2500"}
    $this->assertDatabaseHas('data_loan_lists', [
        'id' => 3,
        'service_id' => 1,
        'name' => '5GB Monthly for N2500',
        'code' => '5000',
        'price' => 2500.00,
    ]);

    // {"id":"4","service_id":"1","name":"10GB Monthly for N5000","code":"GIFT8000","price":"5000"}
    $this->assertDatabaseHas('data_loan_lists', [
        'id' => 4,
        'service_id' => 1,
        'name' => '10GB Monthly for N5000',
        'code' => 'GIFT8000',
        'price' => 5000.00,
    ]);
});
