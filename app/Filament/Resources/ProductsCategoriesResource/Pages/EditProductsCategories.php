<?php

namespace App\Filament\Resources\ProductsCategoriesResource\Pages;

use App\Filament\Resources\ProductsCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductsCategories extends EditRecord
{
    protected static string $resource = ProductsCategoriesResource::class;

    public function getTitle(): string
    {
        return 'Editar Categoria';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
