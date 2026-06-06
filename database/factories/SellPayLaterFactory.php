<?php

namespace Database\Factories;

use App\Models\SellPayLater;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SellPayLater>
 */
class SellPayLaterFactory extends Factory
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
            'product' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'paid' => $this->faker->boolean(),
        ];
    }
}
