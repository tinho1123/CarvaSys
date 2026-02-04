<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FavoredTransaction>
 */
class FavoredTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'company_id' => \App\Models\Company::factory(),
            'client_id' => \App\Models\Client::factory(),
            'name' => $this->faker->name(),
            'description' => $this->faker->optional()->text(),
            'amount' => $this->faker->randomFloat(2, 1, 500),
            'discounts' => 0,
            'total_amount' => $this->faker->randomFloat(2, 1, 500),
            'favored_total' => $this->faker->randomFloat(2, 1, 500),
            'favored_paid_amount' => 0,
            'quantity' => 1,
            'active' => true,
        ];
    }
}
