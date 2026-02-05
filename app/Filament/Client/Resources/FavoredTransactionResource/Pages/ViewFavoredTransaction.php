<?php

namespace App\Filament\Client\Resources\FavoredTransactionResource\Pages;

use App\Filament\Client\Resources\FavoredTransactionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewFavoredTransaction extends ViewRecord
{
    protected static string $resource = FavoredTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pay')
                ->label('Pagar')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->action(fn () => $this->notify('success', 'Redirecionando para o pagamento...'))
                ->requiresConfirmation()
                ->modalHeading('Confirmar Pagamento')
                ->modalDescription('Você será redirecionado para o Stripe para concluir o pagamento deste fiado.')
                ->modalSubmitActionLabel('Confirmar e Pagar'),
        ];
    }
}
