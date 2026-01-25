<?php

namespace App\Filament\Resources\FeeResource\Pages;

use App\Filament\Resources\FeeResource;
use Filament\Resources\Pages\CreateRecord;
use Js;

class CreateFee extends CreateRecord
{
    protected static string $resource = FeeResource::class;

    protected static ?string $title = "Criar Taxa";

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
