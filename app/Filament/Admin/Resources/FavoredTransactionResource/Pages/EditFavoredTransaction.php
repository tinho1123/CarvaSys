<?php

namespace App\Filament\Admin\Resources\FavoredTransactionResource\Pages;

use App\Filament\Admin\Resources\FavoredTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFavoredTransaction extends EditRecord
{
    protected static string $resource = FavoredTransactionResource::class;

    protected static ?string $title = 'Editar Fiado';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
