<?php

namespace App\Filament\Client\Resources\StatementResource\Pages;

use App\Filament\Client\Resources\StatementResource;
use Filament\Resources\Pages\ListRecords;

class ListStatements extends ListRecords
{
    protected static string $resource = StatementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
