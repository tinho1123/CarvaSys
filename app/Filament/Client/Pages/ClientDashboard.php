<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Page;

class ClientDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    protected static ?string $title = 'Dashboard';

    public function mount(): void
    {
        // Verificar se tenant está definido
        if (! app()->bound('current_tenant')) {
            redirect()->route('client.auth.login');
        }
    }
}
