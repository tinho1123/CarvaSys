<?php

namespace App\Http\Middleware;

use App\Models\FavoredTransaction;
use App\Models\Fee;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductsCategories;
use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveTenantScopes
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Safely remove only the tenant 'company' global-scope entries
        // for models that had the scope applied at runtime. We avoid
        // wiping other global scopes by mutating the global-scope
        // registry and unsetting the specific 'company' key per model.

        $models = [
            Transaction::class,
            FavoredTransaction::class,
            Product::class,
            ProductsCategories::class,
            Fee::class,
            Notification::class,
        ];

        foreach ($models as $model) {
            if (! class_exists($model)) {
                continue;
            }

            // Use the HasGlobalScopes API: getAllGlobalScopes / setAllGlobalScopes
            // to remove only the 'company' key for this model, if present.
            try {
                if (method_exists($model, 'getAllGlobalScopes') && method_exists($model, 'setAllGlobalScopes')) {
                    $all = $model::getAllGlobalScopes();

                    if (isset($all[$model]) && is_array($all[$model]) && array_key_exists('company', $all[$model])) {
                        unset($all[$model]['company']);
                        $model::setAllGlobalScopes($all);
                    }
                }
            } catch (\Throwable $e) {
                // Do not break the request if something unexpected happens here.
                // We intentionally swallow errors to avoid causing admin pages
                // to fail during scope cleanup.
                continue;
            }
        }

        // Clear tenant instance from container if present
        if (app()->bound('current_tenant')) {
            app()->forgetInstance('current_tenant');
        }

        return $next($request);
    }
}
