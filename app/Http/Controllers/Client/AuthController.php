<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Mostrar página de login do cliente.
     */
    public function showLoginForm()
    {
        // Se já estiver logado, redirecionar para dashboard
        if (Auth::guard('client')->check()) {
            return redirect('/cliente/dashboard');
        }

        return view('client.auth.login');
    }

    /**
     * Mostrar página de seleção de empresa após login.
     */
    public function showCompanySelection(Request $request)
    {
        $clientUser = Auth::guard('client')->user();

        // Buscar empresas onde o cliente tem fiados ou transações
        // TODO: Implementar quando FavoredDebt e Transaction existirem
        $companies = []; // $clientUser->getCompaniesWithActivity();

        // Verificar se a conta está bloqueada
        if ($clientUser && $clientUser->locked_until && now()->lt($clientUser->locked_until)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Conta temporariamente bloqueada. Tente novamente mais tarde.');
        }

        // Verificar se o cliente pertence à empresa selecionada
        if ($clientUser && $clientUser->company_id != $request->company_id) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Este cliente não pertence à empresa selecionada.');
        }

        // Redirecionar para dashboard
        return view('client.auth.company-selection', compact('companies'));
    }

    /* Processar login do cliente. */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'company_id' => 'required|uuid|exists:companies,uuid',
        ]);
        // Buscar usuário cliente
        $clientUser = ClientUser::with(['client', 'company'])
            ->where('email', $request->email)
            ->first();
        if (! $clientUser || ! Hash::check($request->password, $clientUser->password)) {
            // Incrementar tentativas de login
            if ($clientUser) {
                $clientUser->increment('login_attempts');

                // Bloquear após 5 tentativas
                if ($clientUser->login_attempts >= 5) {
                    $clientUser->update([
                        'locked_until' => now()->addMinutes(30),
                        'login_attempts' => 0,
                    ]);
                }
            }

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciais inválidas ou conta bloqueada.');
        }
        // Verificar se a conta está bloqueada
        if ($clientUser && $clientUser->locked_until && now()->lt($clientUser->locked_until)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Conta temporariamente bloqueada. Tente novamente mais tarde.');
        }
        // Verificar se o cliente pertence à empresa selecionada
        if ($clientUser->company_id != $request->company_id) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Este cliente não pertence à empresa selecionada.');
        }
        // Resetar tentativas e registrar último login
        $clientUser->update([
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);
        // Logar o cliente
        Auth::guard('client')->login($clientUser);
        // Salvar empresa selecionada na sessão
        Session::put('selected_company', $request->company_id);

        // Redirecionar para dashboard
        return redirect('/cliente/dashboard');
    }

    /**
     * Fazer logout do cliente.
     */
    public function logout()
    {
        Auth::guard('client')->logout();
        Session::forget('selected_company');

        return redirect('/cliente/login')
            ->with('success', 'Você saiu com sucesso.');
    }

    /**
     * Mostrar formulário de primeiro acesso.
     */
    public function showFirstAccessForm()
    {
        return view('client.auth.first-access');
    }

    /**
     * Processar primeiro acesso.
     */
    public function firstAccess(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:cpf,cnpj',
            'document_number' => 'required|string',
            'email' => 'required|email|exists:clients,email',
            'company_id' => 'required|uuid|exists:companies,uuid',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Buscar cliente pelo documento e email
        $client = Client::where([
            'document_number' => $request->document_number,
            'email' => $request->email,
        ])->first();

        if (! $client) {
            return back()
                ->withInput()
                ->with('error', 'Cliente não encontrado com os dados informados.');
        }

        // Verificar se já existe usuário para este cliente
        if ($client->user) {
            return back()
                ->withInput()
                ->with('error', 'Este cliente já possui acesso. Utilize a opção "Esqueci minha senha".');
        }

        // Criar usuário cliente
        $clientUser = ClientUser::create([
            'uuid' => Str::uuid(),
            'client_id' => $client->uuid,
            'company_id' => $request->company_id,
            'email' => $client->email,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'password' => Hash::make($request->password),
            'preferences' => [
                'notifications' => [
                    'email' => true,
                    'due_days' => [7, 3, 1],
                ],
                'theme' => 'light',
                'language' => 'pt-BR',
            ],
        ]);

        // Atualizar dados do cliente
        $client->update([
            'email' => $request->email,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
        ]);

        return redirect('/cliente/login')
            ->with('success', 'Acesso criado com sucesso! Faça login para continuar.');
    }

    /**
     * Obter empresas disponíveis para seleção no login.
     */
    public function getCompanies(Request $request)
    {
        $search = $request->get('search');

        $companies = Company::where('active', true)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get(['id', 'uuid', 'name', 'logo'])
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'uuid' => $company->uuid,
                    'name' => $company->name,
                    'logo' => $company->logo ?? null,
                ];
            });

        return response()->json($companies);
    }
}
