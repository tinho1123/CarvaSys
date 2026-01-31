<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FavoredTransactionResource\Pages;
use App\Models\FavoredTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FavoredTransactionResource extends Resource
{
    protected static ?string $model = FavoredTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Fiados de Clientes';

    protected static ?string $navigationGroup = 'Gestão de Clientes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->required()
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nome da Transação'),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3),
                Forms\Components\TextInput::make('favored_total')
                    ->required()
                    ->numeric()
                    ->label('Total do Fiado')
                    ->prefix('R$'),
                Forms\Components\TextInput::make('favored_paid_amount')
                    ->numeric()
                    ->label('Valor Pago')
                    ->prefix('R$'),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->label('Quantidade'),
                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nome'),

                TextColumn::make('favored_total')
                    ->money('BRL')
                    ->sortable()
                    ->label('Total Fiado'),

                TextColumn::make('favored_paid_amount')
                    ->money('BRL')
                    ->sortable()
                    ->label('Valor Pago'),

                TextColumn::make('remaining_balance')
                    ->money('BRL')
                    ->label('Saldo Restante')
                    ->getStateUsing(function ($record) {
                        return $record->getRemainingBalance();
                    }),

                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->label('Quantidade'),

                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->label('Ativo')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFavoredTransactions::route('/'),
            'create' => Pages\CreateFavoredTransaction::route('/create'),
            'edit' => Pages\EditFavoredTransaction::route('/{record}/edit'),
        ];
    }
}
