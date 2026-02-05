<?php

namespace App\Filament\Client\Resources\FavoredTransactionResource\Pages;

use App\Filament\Client\Resources\FavoredTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListFavoredTransactions extends ListRecords
{
    protected static string $resource = FavoredTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
