<?php

namespace Database\Factories;

use App\Models\Companies;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompaniesUsers>
 */
class CompaniesUsersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "company_id" => Companies::factory(),
            "user_id" => User::factory()
        ];
    }
}
