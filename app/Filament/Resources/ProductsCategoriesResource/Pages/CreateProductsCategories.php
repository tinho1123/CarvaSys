<?php

namespace App\Filament\Resources\ProductsCategoriesResource\Pages;

use App\Filament\Resources\ProductsCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductsCategories extends CreateRecord
{
    protected static string $resource = ProductsCategoriesResource::class;

    public function getTitle(): string
    {
        return 'Criar Categoria';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->current_company_id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
