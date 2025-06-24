<?php

namespace App\Filament\Resources\ClientsResource\Pages;

use App\Filament\Resources\ClientsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClients extends EditRecord
{
    protected static string $resource = ClientsResource::class;

    public function getTitle(): string
    {
        return 'Editar Cliente';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
