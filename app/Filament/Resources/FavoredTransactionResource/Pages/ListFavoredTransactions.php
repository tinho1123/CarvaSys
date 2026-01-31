<?php

namespace App\Filament\Resources\FavoredTransactionResource\Pages;

use App\Filament\Resources\FavoredTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListFavoredTransactions extends ListRecords
{
    protected static string $resource = FavoredTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
