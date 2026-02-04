<?php

namespace App\Filament\Client\Pages;

use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class ClientLoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        // Pegar o tenant da sessão
        $tenantId = session('selected_tenant_id');
        
        if ($tenantId) {
            // Redirecionar para o dashboard com o tenant usando response helper
            return response()->redirectTo(
                route('filament.client.pages.client-dashboard', [
                    'tenant' => $tenantId,
                ])
            );
        }

        // Fallback para o padrão do Filament
        return parent::toResponse($request);
    }
}
