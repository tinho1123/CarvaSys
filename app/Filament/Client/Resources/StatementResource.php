<?php

namespace App\Filament\Client\Resources;

use App\Models\Transaction;
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
use App\Filament\Client\Resources\StatementResource\Pages;

class StatementResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Meu Extrato';
    
    protected static ?string $pluralModelLabel = 'Transações';
    
    protected static ?string $modelLabel = 'Transação';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Produto')
                    ->defaultImageUrl(fn ($record) => $record->product?->image)
                    ->circular(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Descrição')
                    ->searchable(),
                TextColumn::make('category_name')
                    ->label('Categoria')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('isCool')
                    ->label('Gelada?')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('info')
                    ->falseColor('gray'),
                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pix' => 'PIX',
                        'cash' => 'Dinheiro',
                        'debit_card' => 'Cartão Débito',
                        'credit_card' => 'Cartão Crédito',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pix' => 'success',
                        'cash' => 'warning',
                        'debit_card' => 'info',
                        'credit_card' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sale' => 'Débito (Compra)',
                        'credit' => 'Crédito',
                        'payment' => 'Pagamento',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'danger',
                        'credit' => 'success',
                        'payment' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('total_amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'sale' => 'Débito (Compra)',
                        'credit' => 'Crédito',
                        'payment' => 'Pagamento',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('Método de Pagamento')
                    ->options([
                        'pix' => 'PIX',
                        'cash' => 'Dinheiro',
                        'debit_card' => 'Cartão Débito',
                        'credit_card' => 'Cartão Crédito',
                    ]),
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
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatements::route('/'),
        ];
    }
}
