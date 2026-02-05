<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ClientTenantResolver
{
    /**
     * Handle an incoming request.
     * Resolve tenant dinamicamente a partir do parâmetro URL sem usar session
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obter tenant UUID do parâmetro de rota
        $tenantUuid = $request->route('tenant');

        // Se não há tenant, pular para próximo middleware
        if (! $tenantUuid) {
            return $next($request);
        }

        // Verificar se cliente está autenticado
        if (! Auth::guard('client')->check()) {
            return $next($request);
        }

        $client = Auth::guard('client')->user();

        // Encontrar a empresa pelo UUID
        $company = \App\Models\Company::where('uuid', $tenantUuid)
            ->orWhere('id', $tenantUuid)
            ->first();

        if (! $company) {
            Log::warning('Company not found', [
                'tenant_uuid' => $tenantUuid,
                'client_id' => $client->id,
            ]);
            abort(404, 'Empresa não encontrada.');
        }

        // Verificar se o cliente tem acesso à empresa
        if (! $client->canAccessTenant($company)) {
            Log::warning('Client cannot access tenant', [
                'client_id' => $client->id,
                'company_id' => $company->id,
            ]);
            abort(403, 'Acesso negado a esta empresa.');
        }

        // Configurar tenant globalmente
        app()->singleton('current_tenant', fn () => $company);

        // Aplicar global scopes de multi-tenancy
        $this->applyTenantScopes($company);

        return $next($request);
    }

    /**
     * Aplicar global scopes em models que requerem isolamento de tenant
     */
    private function applyTenantScopes($company): void
    {
        // Aplicar scope de company em models relevantes
        \App\Models\Transaction::addGlobalScope('company', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });

        \App\Models\FavoredTransaction::addGlobalScope('company', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });

        \App\Models\FavoredDebt::addGlobalScope('company', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });

        \App\Models\Product::addGlobalScope('company', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });

        \App\Models\ProductsCategories::addGlobalScope('company', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });

        \App\Models\Fee::addGlobalScope('company', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });

        \App\Models\Order::addGlobalScope('company', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });
    }
}
