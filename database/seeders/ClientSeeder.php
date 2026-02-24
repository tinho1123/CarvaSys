<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Atualizar/Criar Parceiros (Companies) com metadados de Marketplace
        
        // Restaurante
        $cantina = Company::updateOrCreate(
            ['name' => 'Cantina do Well'],
            [
                'uuid' => Str::uuid(),
                'type' => 'Restaurantes',
                'description' => 'A melhor marmita da região com o tempero caseiro que você ama.',
                'logo_path' => '/marketplace/cantina_logo.png',
                'banner_path' => '/marketplace/cantina_banner.png',
                'rating' => 4.9,
                'delivery_time' => '25-45 min',
                'is_promoted' => true,
                'active' => 'Y',
                'foundation_date' => '2015-05-10',
            ]
        );

        // Mercado
        $mercado = Company::updateOrCreate(
            ['name' => 'Mercado Carvalho'],
            [
                'uuid' => Str::uuid(),
                'type' => 'Mercados',
                'description' => 'Produtos frescos todo dia e entrega rápida direto na sua casa.',
                'logo_path' => '/marketplace/mercado_logo.png',
                'banner_path' => '/marketplace/default_banner.png',
                'rating' => 4.7,
                'delivery_time' => '15-25 min',
                'is_promoted' => false,
                'active' => 'Y',
                'foundation_date' => '2018-09-20',
            ]
        );

        // Farmácia
        $farmacia = Company::updateOrCreate(
            ['name' => 'Farmácia Carva'],
            [
                'uuid' => Str::uuid(),
                'type' => 'Farmácias',
                'description' => 'Cuidando da sua saúde com agilidade e os melhores preços.',
                'logo_path' => '/default-store-logo.png',
                'banner_path' => '/marketplace/default_banner.png',
                'rating' => 4.8,
                'delivery_time' => '10-20 min',
                'is_promoted' => false,
                'active' => 'Y',
                'foundation_date' => '2021-03-15',
            ]
        );

        // Bebidas
        $bebidos = Company::updateOrCreate(
            ['name' => 'Bebidas Geladas'],
            [
                'uuid' => Str::uuid(),
                'type' => 'Bebidas',
                'description' => 'Cervejas, destilados e refrigerantes trincando de gelados.',
                'logo_path' => '/marketplace/bebidas_logo.png',
                'banner_path' => '/marketplace/default_banner.png',
                'rating' => 4.6,
                'delivery_time' => '20-40 min',
                'is_promoted' => true,
                'active' => 'Y',
                'foundation_date' => '2023-11-01',
            ]
        );

        $companies = [$cantina, $mercado, $farmacia, $bebidos];

        // 2. Criar Categoria Globais (Sistema)
        $cats = [
            'Alimentos Básicos',
            'Bebidas Alcoólicas',
            'Bebidas Não Alcoólicas',
            'Carnes e Frios',
            'Hortifruti',
            'Padaria e Laticínios',
            'Snacks e Doces',
            'Higiene Pessoal',
            'Limpeza e Casa',
            'Cuidados com a Saúde',
            'Pet Shop',
            'Bazar e Papelaria'
        ];

        $globalCategories = [];
        foreach ($cats as $catName) {
            $globalCategories[$catName] = \App\Models\ProductsCategories::updateOrCreate(
                ['name' => $catName],
                ['active' => 'Y']
            );
        }

        // 3. Criar Cliente Principal (Marketplace / SSO)
        $wellington = Client::updateOrCreate(
            ['document_number' => '13530365700'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Wellington Carvalho',
                'email' => 'carvalho.cwell@gmail.com',
                'password' => Hash::make('Well.10091999'),
                'document_type' => 'cpf',
                'active' => true, // Client table uses boolean
            ]
        );

        // 4. Associar Dados por Empresa e Gerar Produtos
        foreach ($companies as $company) {
            // Vincular o Wellington a todas as empresas para liberar o acesso SSO/Marketplace
            $wellington->companies()->syncWithoutDetaching([$company->id => ['is_active' => true]]);

            foreach ($globalCategories as $catName => $category) {
                // Evitar duplicação de produtos no re-seed
                $productCount = \App\Models\Product::where('category_id', $category->id)
                    ->where('company_id', $company->id)
                    ->count();

                if ($productCount === 0) {
                    for ($p = 1; $p <= 3; $p++) {
                        $amount = rand(10, 150);
                        $discount = rand(0, 1) ? $amount * 0.1 : 0;

                        \App\Models\Product::create([
                            'uuid' => Str::uuid(),
                            'company_id' => $company->id,
                            'category_id' => $category->id,
                            'name' => "Item $p de $catName (" . $company->name . ")",
                            'description' => "Excelente opção para você e sua família. Qualidade garantida por " . $company->name,
                            'amount' => $amount,
                            'discounts' => $discount,
                            'total_amount' => $amount - $discount,
                            'quantity' => 100,
                            'image' => '/demo-product.png',
                            'active' => 'Y',
                            'isCool' => ($p % 2 == 0) ? 'Y' : 'N',
                        ]);
                    }
                }
            }
            
            // Taxa Padrão por Empresa
            \App\Models\Fee::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'description' => 'Taxa de Serviço',
                ],
                [
                    'uuid' => Str::uuid(),
                    'amount' => 2.50,
                    'type' => 'fixed',
                ]
            );
        }
    }
}
