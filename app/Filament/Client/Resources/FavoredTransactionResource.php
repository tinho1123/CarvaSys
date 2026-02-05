<?php

namespace App\Filament\Client\Resources;

use App\Models\FavoredTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Client\Resources\FavoredTransactionResource\Pages;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;

class FavoredTransactionResource extends Resource
{
    protected static ?string $model = FavoredTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Meu Fiado';
    
    protected static ?string $pluralModelLabel = 'Consumos no Fiado';
    
    protected static ?string $modelLabel = 'Fiado';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data Criada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Data de Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Não definida'),
                TextColumn::make('name')
                    ->label('Descrição')
                    ->searchable(),
                TextColumn::make('favored_total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('Saldo Restante')
                    ->state(fn (FavoredTransaction $record): float => $record->getRemainingBalance())
                    ->money('BRL')
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
                TextColumn::make('is_paid_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (FavoredTransaction $record): string => $record->isFullyPaid() ? 'Pago' : 'Pendente')
                    ->color(fn (string $state): string => match ($state) {
                        'Pago' => 'success',
                        'Pendente' => 'warning',
                    }),
            ])
            ->filters([
                Filter::make('only_pending')
                    ->label('Apenas Pendentes')
                    ->query(fn (Builder $query) => $query->whereRaw('favored_paid_amount < favored_total')),
                Filter::make('only_paid')
                    ->label('Apenas Pagos')
                    ->query(fn (Builder $query) => $query->whereRaw('favored_paid_amount >= favored_total')),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Resumo do Fiado')
                    ->description('Detalhes da transação e status de pagamento')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Horário do Pedido')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('due_date')
                                    ->label('Data de Vencimento')
                                    ->date('d/m/Y'),
                                TextEntry::make('favored_total')
                                    ->label('Valor Total')
                                    ->money('BRL')
                                    ->weight('bold')
                                    ->color('primary'),
                                TextEntry::make('balance')
                                    ->label('Saldo Restante')
                                    ->state(fn (FavoredTransaction $record): float => $record->getRemainingBalance())
                                    ->money('BRL')
                                    ->weight('bold')
                                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                            ]),
                    ]),

                Section::make('Itens do Pedido')
                    ->schema([
                        RepeatableEntry::make('order.items')
                            ->label('')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        ImageEntry::make('product.image')
                                            ->label('Foto')
                                            ->circular(),
                                        TextEntry::make('product_name')
                                            ->label('Produto'),
                                        TextEntry::make('product.isCool')
                                            ->label('Gelado?')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => $state === 'Y' ? 'Sim' : 'Não')
                                            ->color(fn ($state) => $state === 'Y' ? 'info' : 'gray'),
                                        TextEntry::make('quantity')
                                            ->label('Qtd'),
                                        TextEntry::make('unit_price')
                                            ->label('Preço Unit.')
                                            ->money('BRL'),
                                        TextEntry::make('total_amount')
                                            ->label('Total Item')
                                            ->money('BRL')
                                            ->weight('bold'),
                                    ]),
                            ])
                            ->columns(1)
                            ->grid(1),
                    ])
                    ->visible(fn (FavoredTransaction $record) => $record->order_id !== null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFavoredTransactions::route('/'),
            'view' => Pages\ViewFavoredTransaction::route('/{record}'),
        ];
    }
}
