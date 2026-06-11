<?php

namespace Database\Factories;

use App\Models\ElectricityBill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectricityBill>
 */
class ElectricityBillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'references' => $this->faker->unique()->uuid(),
            'provider_id' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'phone_no' => $this->faker->phoneNumber(),
            'meter_no' => $this->faker->numerify('##########'),
            'details' => $this->faker->sentence(),
            'token' => $this->faker->numerify('####-####-####-####'),
        ];
    }
}
