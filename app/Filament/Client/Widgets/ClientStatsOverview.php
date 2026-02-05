<?php

namespace App\Filament\Client\Widgets;

use App\Models\FavoredTransaction;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ClientStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tenant = filament()->getTenant();
        $client = Auth::guard('client')->user();

        if (!$tenant || !$client) {
            return [];
        }

        // Dívida Total Pendente (Saldo Restante)
        $pendingDebt = FavoredTransaction::where('company_id', $tenant->id)
            ->where('client_id', $client->id)
            ->get()
            ->sum(fn ($t) => $t->getRemainingBalance());

        // Total Pago
        $totalPaid = FavoredTransaction::where('company_id', $tenant->id)
            ->where('client_id', $client->id)
            ->sum('favored_paid_amount');

        // Total de Pedidos
        $totalOrders = Order::where('company_id', $tenant->id)
            ->where('client_id', $client->id)
            ->count();

        // Total Gasto no Mês Atual
        $monthlySpending = Order::where('company_id', $tenant->id)
            ->where('client_id', $client->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        return [
            Stat::make('Dívida em Aberto', 'R$ ' . number_format($pendingDebt, 2, ',', '.'))
                ->description('Valor acumulado de fiados pendentes')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger')
                ->url(\App\Filament\Client\Resources\FavoredTransactionResource::getUrl()),
            Stat::make('Total Pago', 'R$ ' . number_format($totalPaid, 2, ',', '.'))
                ->description('Valor total já quitado')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(\App\Filament\Client\Resources\FavoredTransactionResource::getUrl()),
            Stat::make('Pedidos Realizados', $totalOrders)
                ->description('Total de pedidos nesta empresa')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
            Stat::make('Gasto Mensal', 'R$ ' . number_format($monthlySpending, 2, ',', '.'))
                ->description('Total em pedidos neste mês')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
