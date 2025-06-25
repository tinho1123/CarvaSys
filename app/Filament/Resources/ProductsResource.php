<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductsResource\Pages;
use App\Filament\Resources\ProductsResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Produtos";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('company_id'),
                Forms\Components\FileUpload::make('image')
                    ->label('Imagem')
                    ->image()
                    ->columnSpanFull()
                    ->directory('products')
                    ->disk('public')
                    ->maxSize(2048),
                Forms\Components\Select::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'description')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->label('Valor')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->debounce(500)
                    ->afterStateUpdated(
                        fn($state, callable $set, callable $get) =>
                        static::atualizarTotal($set, $get)
                    ),
                Forms\Components\TextInput::make('discounts')
                    ->label('Descontos')
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
                    ->label('Valor total')
                    ->prefix('R$')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(true)
                    ->extraAttributes([
                        'wire:loading.class' => 'opacity-50',
                        'wire:target' => 'amount,discounts',
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('isCool')
                    ->label('Gelado?')
                    ->onColor('success')
                    ->offColor('danger')
                    ->dehydrateStateUsing(fn(bool $state) => $state ? 'Y' : 'N')
                    ->formatStateUsing(fn($state) => $state === 'Y')
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
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
                Tables\Columns\ToggleColumn::make('active')
                    ->label('Ativo')
                    ->onColor('success')
                    ->offColor('danger')
                    ->getStateUsing(fn($record) => $record->active === 'Y')
                    ->disabled(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->rounded()
                    ->label('Imagem')
                    ->disk('public'),
                Tables\Columns\ToggleColumn::make('isCool')
                    ->label('Gelado?')
                    ->onColor('success')
                    ->offColor('danger')
                    ->getStateUsing(fn($record) => $record->active === 'Y')
                    ->disabled(),
                Tables\Columns\TextColumn::make('category.description')
                    ->label('Categoria')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
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
