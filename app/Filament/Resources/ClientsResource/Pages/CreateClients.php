<?php

namespace App\Filament\Resources\ClientsResource\Pages;

use App\Filament\Resources\ClientsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClients extends CreateRecord
{
    protected static string $resource = ClientsResource::class;

    public function getTitle(): string
    {
        return 'Criar Cliente';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->current_company_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
