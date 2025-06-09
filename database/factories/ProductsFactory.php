<?php

namespace Database\Factories;

use App\Models\CompaniesUsers;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "companies_users" => CompaniesUsers::factory(),
            "name" => fake()->name(),
            "description" => fake()->streetName(),
            "quantity"  => fake()->randomNumber(),
        ];
    }
}
