<?php

namespace App\Filament\Resources\ProductsCategoriesResource\Pages;

use App\Filament\Resources\ProductsCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductsCategories extends ListRecords
{
    protected static string $resource = ProductsCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
