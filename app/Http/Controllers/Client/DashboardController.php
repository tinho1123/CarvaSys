<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard do cliente.
     */
    public function index()
    {
        $clientUser = Auth::guard('client')->user();
        $client = $clientUser->client;
        $company = $clientUser->company;

        // TODO: Implementar com os models reais quando criados
        $totalFiados = 0; // $client->favoredDebts()->where('status', '!=', 'paid')->sum('final_total');
        $proximosPagamentos = 0; // $client->favoredDebts()->whereBetween('due_date', [now(), now()->addDays(7)])->count();
        $transacoesMes = 0; // $client->transactions()->whereMonth('created_at', now()->month)->count();
        $notificacoesNaoLidas = $clientUser->unreadNotifications()->count();

        return view('client.dashboard', compact(
            'client',
            'company',
            'totalFiados',
            'proximosPagamentos',
            'transacoesMes',
            'notificacoesNaoLidas'
        ));
    }

    /**
     * Mostrar lista de fiados.
     */
    public function fiados()
    {
        $clientUser = Auth::guard('client')->user();
        $client = $clientUser->client;

        // TODO: Implementar quando FavoredDebt existir
        $fiados = []; // $client->favoredDebts()->with(['transactions'])->paginate(10);

        return view('client.fiados', compact('client', 'fiados'));
    }

    /**
     * Mostrar lista de transações.
     */
    public function transacoes()
    {
        $clientUser = Auth::guard('client')->user();
        $client = $clientUser->client;

        // TODO: Implementar quando Transaction existir
        $transacoes = []; // $client->transactions()->paginate(15);

        return view('client.transacoes', compact('client', 'transacoes'));
    }

    /**
     * Mostrar calendário de pagamentos.
     */
    public function pagamentos()
    {
        $clientUser = Auth::guard('client')->user();
        $client = $clientUser->client;

        // TODO: Implementar quando FavoredDebt existir
        $pagamentos = []; // $client->favoredDebts()->where('due_date', '>=', now())->paginate(15);

        return view('client.pagamentos', compact('client', 'pagamentos'));
    }

    /**
     * Mostrar notificações.
     */
    public function notificacoes()
    {
        $clientUser = Auth::guard('client')->user();

        // TODO: Implementar quando FavoredDebt existir
        $notificacoesRecentes = []; // $clientUser->notifications()->orderBy('created_at', 'desc')->take(20)->get();
        $vencimentosProximos = []; // $client->favoredDebts()->whereBetween('due_date', [now(), now()->addDays(7)])->get();
        $notificacoesPagamento = []; // $clientUser->notifications()->where('type', 'like', 'payment_%')->get();

        return view('client.notificacoes', compact(
            'clientUser',
            'notificacoesRecentes',
            'vencimentosProximos',
            'notificacoesPagamento'
        ));
    }

    /**
     * Mostrar perfil do cliente.
     */
    public function perfil()
    {
        $clientUser = Auth::guard('client')->user();
        $client = $clientUser->client;

        return view('client.perfil', compact('clientUser', 'client'));
    }

    /**
     * Marcar notificação como lida.
     */
    public function markNotificationAsRead($notificationId)
    {
        $clientUser = Auth::guard('client')->user();
        $notification = $clientUser->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas as notificações como lidas.
     */
    public function markAllNotificationsAsRead()
    {
        $clientUser = Auth::guard('client')->user();

        $clientUser->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
