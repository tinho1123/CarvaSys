<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(1)->create([
            "email" => "tinhoefaela@gmail.com",
            "name" => "Wellington Carvalho da Cunha Filho",
            "password" => "123456",
            "phone_number" => "+5521991751952"
        ]);
        \App\Models\Companies::factory(1)->create([
            'name' => "KWR Depósito de bebidas"
        ]);
        \App\Models\CompaniesUsers::factory(1)->create();
        \App\Models\Products::factory(10)->create();
    }
}
