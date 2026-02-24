<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\Facades\Cache;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    protected static ?string $title = 'Criar Produto';

    public function mount(): void
    {
        parent::mount();

        $cacheKey = 'product_create_form_' . auth()->id();
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            $this->form->fill($cachedData);
        }
    }

    public function updated($propertyName): void
    {
        $cacheKey = 'product_create_form_' . auth()->id();
        Cache::put($cacheKey, $this->form->getState(), 3600); // 1 hour TTL
    }

    protected function getAfterCreateRedirectUrl(): string
    {
        Cache::forget('product_create_form_' . auth()->id());

        return $this->getResource()::getUrl('index');
    }
}
