<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se usuário está autenticado como cliente
        if (! Auth::guard('client')->check()) {
            return redirect()->route('client.login');
        }

        // Verificar se a conta está bloqueada
        $user = Auth::guard('client')->user();
        if ($user && $user->locked_until && now()->lt($user->locked_until)) {
            Auth::guard('client')->logout();

            return redirect()->route('client.login')
                ->with('error', 'Conta temporariamente bloqueada. Tente novamente mais tarde.');
        }

        return $next($request);
    }
}
