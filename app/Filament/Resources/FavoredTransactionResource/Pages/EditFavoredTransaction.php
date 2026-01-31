<?php

namespace App\Filament\Resources\FavoredTransactionResource\Pages;

use App\Filament\Resources\FavoredTransactionResource;
use Filament\Resources\Pages\EditRecord;

class EditFavoredTransaction extends EditRecord
{
    protected static string $resource = FavoredTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
