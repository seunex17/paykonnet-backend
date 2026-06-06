<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VirtualCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VirtualCard>
 */
class VirtualCardFactory extends Factory
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
            'card_id' => $this->faker->uuid(),
            'account_id' => $this->faker->uuid(),
            'card_type' => 'VISA',
            'currency' => 'NGN',
            'card_pan' => $this->faker->creditCardNumber(),
            'masked_pan' => '4111********1111',
            'cvv' => '123',
            'expiration' => '12/26',
            'name_on_card' => $this->faker->name(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'address' => $this->faker->address(),
            'is_active' => true,
            'is_block' => false,
        ];
    }
}
