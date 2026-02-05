<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class SSOCallbackController extends Controller
{
    /**
     * Handle the response from Clerk and sync with local database.
     */
    public function __invoke(Request $request)
    {
        // Esta rota será acessada pelo React após o Clerk autenticar
        // O frontend enviará os dados do usuário do Clerk
        $clerkId = $request->input('clerk_id');
        $email = $request->input('email');
        $name = $request->input('name');

        if (!$clerkId) {
            \Log::warning('SSO Callback acessado sem clerk_id');
            return redirect()->route('marketplace.index');
        }

        \Log::info('Iniciando sincronização SSO para Clerk ID: ' . $clerkId);

        // 1. Verificar se o cliente já existe pelo clerk_id
        $client = Client::where('clerk_id', $clerkId)->first();

        // 2. Se não existir pelo ID, tentar pelo e-mail
        if (!$client && $email) {
            $client = Client::where('email', $email)->first();
            if ($client) {
                \Log::info('Cliente encontrado por e-mail. Vinculando Clerk ID.');
                $client->update(['clerk_id' => $clerkId]);
            }
        }

        // 3. Se ainda não existir, criar um registro temporário/novo
        if (!$client) {
            \Log::info('Criando novo cliente para Clerk ID: ' . $clerkId);
            $client = Client::create([
                'uuid' => (string) Str::uuid(),
                'clerk_id' => $clerkId,
                'name' => $name,
                'email' => $email,
                'active' => true,
                'document_type' => 'cpf', 
            ]);
        }

        // 4. LOGAR O CLIENTE NO LARAVEL
        auth()->guard('client')->login($client);
        \Log::info('Cliente autenticado no Laravel: ' . $client->name);

        // 5. Verificar se o CPF está preenchido
        if (empty($client->document_number)) {
            \Log::info('Perfil incompleto. Solicitando CPF.');
            return response()->json([
                'status' => 'incomplete_profile',
                'redirect_url' => route('marketplace.complete-profile')
            ]);
        }

        \Log::info('Perfil completo. Redirecionando para Home.');
        return response()->json([
            'status' => 'success',
            'redirect_url' => route('marketplace.index')
        ]);
    }

    public function completeProfile()
    {
        return Inertia::render('Marketplace/CompleteProfile');
    }

    public function storeProfile(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string|size:11', // Validação bruta por enquanto
            'clerk_id' => 'required|string',
        ]);

        $client = Client::where('clerk_id', $request->clerk_id)->firstOrFail();
        
        $client->update([
            'document_number' => $request->cpf,
            'active' => true,
        ]);

        return redirect()->route('marketplace.index');
    }
}
