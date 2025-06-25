<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function getTitle():string
    {
        return 'Transações';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Criar transação'),
        ];
    }
}
