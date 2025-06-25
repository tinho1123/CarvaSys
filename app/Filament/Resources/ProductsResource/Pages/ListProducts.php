<?php

namespace App\Filament\Resources\ProductsResource\Pages;

use App\Filament\Resources\ProductsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductsResource::class;

    protected static ?string $title = "Produtos";

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Criar produto'),
        ];
    }
}
