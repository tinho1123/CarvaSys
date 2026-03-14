<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FavoredTransactionResource\Pages;
use App\Models\Client;
use App\Models\FavoredTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FavoredTransactionResource extends Resource
{
    protected static ?string $model = FavoredTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Fiados';

    protected static ?string $modelLabel = 'Fiado';

    protected static ?string $pluralModelLabel = 'Fiados';

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->options(Client::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('favored_total')
                            ->label('Total do Fiado')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),
                        Forms\Components\TextInput::make('favored_paid_amount')
                            ->label('Valor Pago')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('favored_total')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('favored_paid_amount')
                    ->label('Pago')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Saldo Restante')
                    ->state(fn (FavoredTransaction $record): float => $record->getRemainingBalance())
                    ->sortable(false),
                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
