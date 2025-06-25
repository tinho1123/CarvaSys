<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Transações";

    protected static ?string $breadcrumb = "Transações";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('company_id'),
                Forms\Components\Select::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->debounce(500)
                    ->afterStateUpdated(function (\Filament\Forms\Set $set, ?string $state) {
                        if ($state) {
                            $product = \App\Models\Product::find($state);
                            if ($product) {
                                $set('name', $product->name);
                                $set('description', $product->description);
                                $set('image', $product->image);
                            }
                        }
                    })->columnSpanFull(),
                    Forms\Components\FileUpload::make('image')
                    ->image()->disabled()->columnSpanFull(),
                Forms\Components\Select::make('fees_id')
                    ->label('Taxa')
                    ->relationship('fee', 'description')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->label('Valor')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('discounts')
                    ->label('Descontos')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('fees')
                    ->label('Taxa')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Valor Total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade')
                    ->required()
                    ->numeric(),

                Forms\Components\Toggle::make('isCool')
                    ->label('Gelado?')
                    ->required(),
                Forms\Components\TextInput::make('category_name')
                    ->label('Nome da Categoria')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('category_id')
                    ->required(),
                Forms\Components\TextInput::make('client_name')
                    ->label('Nome do Cliente')
                    ->maxLength(255),
                Forms\Components\Hidden::make('client_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discounts')
                    ->label('Descontos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fees')
                    ->label('Taxa')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Valor Total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                ->label('Imagem')
                ->rounded(),
                Tables\Columns\ToggleColumn::make('isCool')
                ->label('Gelado?'),
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Categoria')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
