<?php

namespace App\Filament\Client\Widgets;

use App\Models\FavoredTransaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ClientDebtChart extends ChartWidget
{
    protected static ?string $heading = 'Status de Pagamento (Consolidado)';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $tenant = filament()->getTenant();
        $client = Auth::guard('client')->user();

        if (!$tenant || !$client) {
            return [];
        }

        $totalPaid = FavoredTransaction::where('company_id', $tenant->id)
            ->where('client_id', $client->id)
            ->sum('favored_paid_amount');

        $totalDebt = FavoredTransaction::where('company_id', $tenant->id)
            ->where('client_id', $client->id)
            ->get()
            ->sum(fn ($t) => $t->getRemainingBalance());

        return [
            'datasets' => [
                [
                    'label' => 'Valor',
                    'data' => [$totalPaid, $totalDebt],
                    'backgroundColor' => [
                        '#10b981', // green-500
                        '#ef4444', // red-500
                    ],
                ],
            ],
            'labels' => ['Total Pago', 'Total Pendente'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
