<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Vendas';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', Order::STATUS_PENDING)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        Order::STATUS_PENDING => 'Pendente',
                        Order::STATUS_PROCESSING => 'Em separação',
                        Order::STATUS_SHIPPED => 'A caminho',
                        Order::STATUS_DELIVERED => 'Finalizado',
                        Order::STATUS_CANCELLED => 'Cancelado',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações do Pedido')
                    ->schema([
                        Infolists\Components\TextEntry::make('uuid')
                            ->label('ID do Pedido')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('client.name')
                            ->label('Cliente'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Order::STATUS_PENDING => 'warning',
                                Order::STATUS_PROCESSING => 'info',
                                Order::STATUS_SHIPPED => 'primary',
                                Order::STATUS_DELIVERED => 'success',
                                Order::STATUS_CANCELLED => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Order::STATUS_PENDING => 'Pendente',
                                Order::STATUS_PROCESSING => 'Em separação',
                                Order::STATUS_SHIPPED => 'A caminho',
                                Order::STATUS_DELIVERED => 'Finalizado',
                                Order::STATUS_CANCELLED => 'Cancelado',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Data do Pedido')
                            ->dateTime(),
                    ])->columns(2),

                Infolists\Components\Section::make('Itens do Pedido')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('Produto'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Quantidade'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('Preço Unitário')
                                    ->money('BRL'),
                                Infolists\Components\TextEntry::make('total_amount')
                                    ->label('Subtotal')
                                    ->money('BRL'),
                            ])->columns(4),
                    ]),

                Infolists\Components\Section::make('Totais')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('BRL'),
                        Infolists\Components\TextEntry::make('discount_amount')
                            ->label('Descontos')
                            ->money('BRL'),
                        Infolists\Components\TextEntry::make('fee_amount')
                            ->label('Taxas')
                            ->money('BRL'),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total Final')
                            ->money('BRL')
                            ->weight('bold')
                            ->size('lg'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('ID do Pedido')
                    ->searchable()
                    ->copyable()
                    ->limit(8),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_PROCESSING => 'info',
                        Order::STATUS_SHIPPED => 'primary',
                        Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'Pendente',
                        Order::STATUS_PROCESSING => 'Em separação',
                        Order::STATUS_SHIPPED => 'A caminho',
                        Order::STATUS_DELIVERED => 'Finalizado',
                        Order::STATUS_CANCELLED => 'Cancelado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PENDING => 'Pendente',
                        Order::STATUS_PROCESSING => 'Em separação',
                        Order::STATUS_SHIPPED => 'A caminho',
                        Order::STATUS_DELIVERED => 'Finalizado',
                        Order::STATUS_CANCELLED => 'Cancelado',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Order $record): bool => $record->canBeApproved())
                    ->action(fn (Order $record) => $record->approve())
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('ship')
                    ->label('Despachar')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(fn (Order $record): bool => $record->canBeShipped())
                    ->action(fn (Order $record) => $record->ship())
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrders::route('/'),
        ];
    }
}
