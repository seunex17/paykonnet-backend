<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_number' => $this->faker->unique()->bankAccountNumber(),
            'bank_name' => $this->faker->company().' Bank',
            'references' => [
                'provider' => $this->faker->word(),
                'external_id' => $this->faker->uuid(),
            ],
        ];
    }
}
