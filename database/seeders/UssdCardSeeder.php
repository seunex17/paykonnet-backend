<?php

namespace Database\Seeders;

use App\Models\UssdCard;
use Illuminate\Database\Seeder;

class UssdCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UssdCard::factory()->count(10)->create();
    }
}
