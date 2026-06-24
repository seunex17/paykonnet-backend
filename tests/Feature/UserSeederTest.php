<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user seeder seeds data correctly from json', function () {
    // Run the seeder
    $this->seed(UserSeeder::class);

    // Assert that the data was seeded
    expect(User::count())->toBeGreaterThan(0);

    // Verify first record from JSON
    // {"id":"1","uuid":"6a2e35ea-2eca-4543-a4e6-1cf6821f9cf3","firstname":"lorem","lastname":"zuma","email":"user001@mailingator.com",...}
    $this->assertDatabaseHas('users', [
        'id' => 1,
        'uuid' => '6a2e35ea-2eca-4543-a4e6-1cf6821f9cf3',
        'firstname' => 'lorem',
        'lastname' => 'zuma',
        'name' => 'lorem zuma',
        'email' => 'user001@mailingator.com',
        'id_verified' => false,
        'agent_level' => '0',
    ]);

    // Verify record with id_verified = 1 and agent_level = 1
    // {"id":"10","uuid":"218749fe-37f4-4fae-8837-3693560c0bf3","firstname":"Oluwapelumi","lastname":"Atowoju","email":"smallrich14@gmail.com","username":"Haaweboy",...}
    $this->assertDatabaseHas('users', [
        'id' => 10,
        'uuid' => '218749fe-37f4-4fae-8837-3693560c0bf3',
        'firstname' => 'Oluwapelumi',
        'lastname' => 'Atowoju',
        'name' => 'Oluwapelumi Atowoju',
        'email' => 'smallrich14@gmail.com',
        'id_verified' => true,
        'agent_level' => '1',
    ]);
});
