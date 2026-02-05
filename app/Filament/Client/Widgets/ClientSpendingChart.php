<?php

namespace App\Filament\Client\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ClientSpendingChart extends ChartWidget
{
    protected static ?string $heading = 'Meus Gastos Mensais';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $tenant = filament()->getTenant();
        $client = Auth::guard('client')->user();

        if (!$tenant || !$client) {
            return [];
        }

        // Se o pacote Trend não estiver instalado, faremos uma query manual simples
        // Pelo composer.json eu vi que não tem Trend instalado, então vou usar Eloquent manual.
        
        $data = Order::where('company_id', $tenant->id)
            ->where('client_id', $client->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('SUM(total_amount) as total, MONTH(created_at) as month, YEAR(created_at) as year')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $labels = $data->map(fn ($d) => date('M/Y', mktime(0, 0, 0, $d->month, 10, $d->year)))->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Gasto (R$)',
                    'data' => $values,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
