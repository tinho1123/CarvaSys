<?php

namespace App\Filament\Resources\FavoredTransactionResource\Pages;

use App\Filament\Resources\FavoredTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFavoredTransactions extends ListRecords
{
    protected static string $resource = FavoredTransactionResource::class;

    protected static ?string $title = 'Fiados de Clientes';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Criar Fiado de Cliente'),
        ];
    }
}
