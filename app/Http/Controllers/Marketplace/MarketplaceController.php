<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        
        $companies = Company::where('active', 'Y')
            ->when($category, function ($query, $category) {
                return $query->where('type', $category);
            })
            ->get()
            ->map(fn ($company) => [
                'uuid' => $company->uuid,
                'name' => $company->name,
                'type' => $company->type,
                'logo' => $company->logo_path ?? '/default-store-logo.png',
                'banner' => $company->banner_path ?? '/default-store-banner.png',
                'rating' => $company->rating,
                'delivery_time' => $company->delivery_time ?? '20-30 min',
                'is_promoted' => $company->is_promoted,
            ]);

        $promotedProducts = Product::whereHas('company', fn($q) => $q->where('active', 'Y'))
            ->where('discounts', '>', 0) // Usando a coluna correta 'discounts'
            ->limit(8)
            ->get();

        $lastVisitedUuids = session()->get('last_visited_stores', []);
        $lastVisited = Company::whereIn('uuid', $lastVisitedUuids)
            ->get()
            ->sortBy(fn($c) => array_search($c->uuid, $lastVisitedUuids));

        return Inertia::render('Marketplace/Index', [
            'companies' => $companies,
            'promotedProducts' => $promotedProducts,
            'lastVisited' => $lastVisited,
            'selectedCategory' => $category,
            'categories' => ['Restaurantes', 'Mercados', 'Farmácias', 'Bebidas'],
        ]);
    }

    public function show(Company $company)
    {
        // Registrar visita
        $lastVisited = session()->get('last_visited_stores', []);
        $lastVisited = array_diff($lastVisited, [$company->uuid]); // Remover se já existe
        array_unshift($lastVisited, $company->uuid); // Adicionar no início
        $lastVisited = array_slice($lastVisited, 0, 5); // Manter as últimas 5
        session()->put('last_visited_stores', $lastVisited);

        $company->load(['products' => fn($q) => $q->where('active', 'Y')->with('category')]);

        return Inertia::render('Marketplace/Show', [
            'company' => [
                'uuid' => $company->uuid,
                'name' => $company->name,
                'description' => $company->description,
                'type' => $company->type,
                'logo' => $company->logo_path ?? '/default-store-logo.png',
                'banner' => $company->banner_path ?? '/default-store-banner.png',
                'rating' => $company->rating,
                'delivery_time' => $company->delivery_time ?? '20-30 min',
            ],
            'productsByCategory' => $company->products->groupBy('category.name'),
        ]);
    }
}
