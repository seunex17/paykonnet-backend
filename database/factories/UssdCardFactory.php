<?php

namespace Database\Factories;

use App\Models\UssdCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UssdCard>
 */
class UssdCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'number' => $this->faker->unique()->numerify('################'),
            'identifier' => $this->faker->uuid(),
            'is_valid' => true,
            'validated' => false,
            'service' => $this->faker->word(),
            'code' => $this->faker->bothify('??##??'),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}
