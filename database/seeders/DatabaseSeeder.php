<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\ProductCategoryEnum;
use App\Models\Companies;
use App\Models\CompaniesUsers;
use App\Models\ProductsCategories;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $company = Companies::create([
            "uuid" => Str::uuid(),
            "name" => "Carvalho Soluções em TI",
            "foundation_date" => '1999-09-10'
        ]);
        $user = User::create([
            "uuid" => Str::uuid(),
            "email" => "carvalho.cwell@gmail.com",
            "name" => "Wellington Carvalho da Cunha Filho",
            "phone_number" => "+5521991751952",
            "birth_date" => "1999-09-10",
            'ip_address' => "0.0.0.0",
            'password' => Hash::make('Well.10091999')
        ]);
        $companies_users = CompaniesUsers::create([
            "company_id"  => $company->id,
            "user_id" => $user->id,
        ]);

        foreach (ProductCategoryEnum::cases() as $case) {
            ProductsCategories::updateOrCreate(
                ['id' => $case->value],
                ['description' => strtolower(str_replace('_', ' ', $case->name))]
            );
        }

        \App\Models\Products::factory(10)->create([
            "companies_users" => $companies_users->id
        ]);
    }
}
