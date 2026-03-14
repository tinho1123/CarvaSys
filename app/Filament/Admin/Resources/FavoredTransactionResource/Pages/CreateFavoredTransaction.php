<?php

namespace App\Filament\Admin\Resources\FavoredTransactionResource\Pages;

use App\Filament\Admin\Resources\FavoredTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFavoredTransaction extends CreateRecord
{
    protected static string $resource = FavoredTransactionResource::class;

    protected static ?string $title = 'Criar Fiado';
}
