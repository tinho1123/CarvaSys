<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Lojas';

    protected static ?string $modelLabel = 'Loja';

    protected static ?string $pluralModelLabel = 'Lojas';

    protected static ?string $navigationGroup = 'Master';

    public static function canAccess(): bool
    {
        return auth()->user()?->isMaster() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nome da loja')
                ->required()
                ->maxLength(255),

            TextInput::make('admin_email')
                ->label('E-mail do administrador')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('admin_password')
                ->label('Senha do administrador')
                ->password()
                ->required()
                ->minLength(8),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Nome')
                ->searchable()
                ->sortable(),

            TextColumn::make('users.email')
                ->label('Administrador')
                ->searchable(),

            IconColumn::make('active')
                ->label('Ativa')
                ->boolean(),

            TextColumn::make('created_at')
                ->label('Criada em')
                ->dateTime('d/m/Y')
                ->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
        ];
    }
}
