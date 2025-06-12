<?php

namespace Database\Factories;

use App\Enums\ProductCategoryEnum;
use App\Models\CompaniesUsers;
use App\Models\ProductsCategories;
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
            $amount = fake()->randomFloat(2,10, 1000);
            $discounts = fake()->randomFloat(2, 0, $amount);
        return [
            "companies_users" => CompaniesUsers::factory(),
            "name" => fake()->name(),
            "description" => fake()->streetName(),
            "quantity"  => fake()->randomNumber(),
            'amount' => $amount,
            'discounts' => $discounts,
            'total_amount' => $amount - $discounts,
            'isCool' => 'Y',
            'image' => fake()->imageUrl(640,480, 'food'),
            'category_id' => fake()->randomElement(ProductCategoryEnum::cases())->value
        ];
    }
}
