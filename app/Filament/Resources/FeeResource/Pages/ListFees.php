<?php

namespace App\Filament\Resources\FeeResource\Pages;

use App\Filament\Resources\FeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFees extends ListRecords
{
    protected static string $resource = FeeResource::class;

    protected static ?string $title = "Taxas";

    protected static ?string $breadcrumb = 'Listar Taxas';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Criar Taxa'),
        ];
    }
}
