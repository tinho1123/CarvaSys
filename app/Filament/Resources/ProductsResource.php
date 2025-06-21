<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductsResource\Pages;
use App\Filament\Resources\ProductsResource\RelationManagers;
use App\Models\Products;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsResource extends Resource
{
    protected static ?string $model = Products::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Produtos";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('company_id'),
                Forms\Components\FileUpload::make('image')
                    ->label('imagem')
                    ->image()
                    ->columnSpanFull(),
                Forms\Components\Select::make('category_id')
                    ->label('categoria')
                    ->relationship('category', 'description')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('nome')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('descrição')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->label('valor')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->debounce(500)
                    ->afterStateUpdated(
                        fn($state, callable $set, callable $get) =>
                        static::atualizarTotal($set, $get)
                    ),
                Forms\Components\TextInput::make('discounts')
                    ->label('descontos')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->prefix('R$')
                    ->debounce(500)
                    ->afterStateUpdated(
                        fn($state, callable $set, callable $get) =>
                        static::atualizarTotal($set, $get)
                    ),

                Forms\Components\TextInput::make('total_amount')
                    ->label('valor total')
                    ->prefix('R$')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(true)
                    ->extraAttributes([
                        'wire:loading.class' => 'opacity-50',
                        'wire:target' => 'amount,discounts',
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->label('quantidade')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('isCool')
                    ->label('gelado?')
                    ->onColor('success')
                    ->offColor('danger')
                    ->dehydrateStateUsing(fn(bool $state) => $state ? 'Y' : 'N')
                    ->formatStateUsing(fn($state) => $state === 'Y')
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->label('ativo')
                    ->required()
                    ->default('Y')
                    ->onColor('success')
                    ->offColor('danger')
                    ->formatStateUsing(fn($state) => $state === 'Y')
                    ->dehydrateStateUsing(fn(bool $state) => $state ? 'Y' : 'N')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discounts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('active'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('isCool'),
                Tables\Columns\TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make('uuid'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProducts::route('/create'),
            'edit' => Pages\EditProducts::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function atualizarTotal(callable $set, callable $get): void
    {
        $valor = floatval($get('amount') ?? 0);
        $desconto = floatval($get('discounts') ?? 0);

        $total = max(0, $valor - $desconto);

        $set('total_amount', $total);
    }
}
