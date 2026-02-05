<?php

namespace App\Filament\Client\Widgets;

use App\Models\FavoredTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Minhas Últimas Atividades';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenant = filament()->getTenant();
        $client = Auth::guard('client')->user();

        return $table
            ->query(
                FavoredTransaction::where('company_id', $tenant->id)
                    ->where('client_id', $client->id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('ValorTotal')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('favored_paid_amount')
                    ->label('Valor Pago')
                    ->money('BRL')
                    ->color('success'),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo Restante')
                    ->state(fn ($record) => $record->getRemainingBalance())
                    ->money('BRL')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
            ]);
    }
}
