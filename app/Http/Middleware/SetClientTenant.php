<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetClientTenant
{
    /**
     * Handle an incoming request.
     * Resolve tenant AFTER client authentication (from session or request).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip this middleware for login/logout pages
        $routeName = $request->route()?->getName();
        if ($routeName && (str_contains($routeName, 'auth.login') || str_contains($routeName, 'auth.logout'))) {
            return $next($request);
        }

        // Also skip if the path is login or logout
        if ($request->is('client-admin/login') || $request->is('client-admin/logout')) {
            return $next($request);
        }

        // Se está autenticado, processar o tenant
        if (Auth::guard('client')->check()) {
            // Procurar o tenant no parâmetro da URL (aceita 'tenant' ou 'company')
            $tenantId = $request->route('tenant') ?? $request->route('company');

            if ($tenantId) {
                // Aceita tanto UUID quanto ID
                $tenant = \App\Models\Company::where('uuid', $tenantId)
                    ->orWhere('id', $tenantId)
                    ->first();

                $clientUser = Auth::guard('client')->user();
                \Illuminate\Support\Facades\Log::info('SetClientTenant Debug', [
                    'tenantId' => $tenantId,
                    'tenant_found' => !!$tenant,
                    'tenant_id' => $tenant?->id,
                    'user_id' => $clientUser?->id,
                    'user_email' => $clientUser?->email,
                    'can_access' => $tenant ? $this->canAccessTenant($tenant) : false,
                ]);

                if ($tenant && $this->canAccessTenant($tenant)) {
                    // Definir o tenant globalmente
                    app()->singleton('current_tenant', fn () => $tenant);
                    Session::put('selected_tenant_id', $tenant->uuid);

                    // Aplicar tenant scopes globais
                    $this->applyTenantScopes($tenant);
                } else {
                    // Tenant inválido ou sem acesso - retornar 403
                    abort(403, 'Unauthorized access to this company.');
                }
            }
            // Se não há tenant na URL, simplesmente deixar passar
            // O Filament vai redirecionar automaticamente se necessário
        }

        return $next($request);
    }

    /**
     * Verificar se o ClientUser atual pode acessar o tenant (empresa).
     */
    private function canAccessTenant($tenant): bool
    {
        $clientUser = Auth::guard('client')->user();

        if (! $clientUser) {
            return false;
        }

        // Verificar através do relacionamento N:N client_company
        return $clientUser->companies()
            ->where('companies.id', $tenant->id)
            ->where('client_company.is_active', true)
            ->exists();
    }

    /**
     * Aplicar global scopes de tenant em models relevantes.
     */
    private function applyTenantScopes($tenant): void
    {
        // Transaction
        \App\Models\Transaction::addGlobalScope('company', function ($query) use ($tenant) {
            $query->where('company_id', $tenant->id);
        });

        // FavoredTransaction
        \App\Models\FavoredTransaction::addGlobalScope('company', function ($query) use ($tenant) {
            $query->where('company_id', $tenant->id);
        });

        // Product
        \App\Models\Product::addGlobalScope('company', function ($query) use ($tenant) {
            $query->where('company_id', $tenant->id);
        });

        // ProductsCategories
        \App\Models\ProductsCategories::addGlobalScope('company', function ($query) use ($tenant) {
            $query->where('company_id', $tenant->id);
        });

        // Fee
        \App\Models\Fee::addGlobalScope('company', function ($query) use ($tenant) {
            $query->where('company_id', $tenant->id);
        });

        // Notification
        if (class_exists(\App\Models\Notification::class)) {
            \App\Models\Notification::addGlobalScope('company', function ($query) use ($tenant) {
                $query->where('company_id', $tenant->id);
            });
        }
    }
}
