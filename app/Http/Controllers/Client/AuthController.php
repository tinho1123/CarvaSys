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
            return redirect('/client/dashboard');
        }

        return view('client.auth.login');
    }

    /**
     * Selecionar empresa após autenticação.
     * Define o tenant na sessão e redireciona para dashboard.
     */
    public function selectCompany($companyUuid)
    {
        $clientUser = Auth::guard('client')->user();

        if (! $clientUser) {
            return redirect('/client/login')
                ->with('error', 'Você precisa fazer login primeiro.');
        }

        // Verificar se ClientUser tem acesso à empresa selecionada
        $company = $clientUser->companies()
            ->where('companies.uuid', $companyUuid)
            ->where('client_company.is_active', true)
            ->first();

        if (! $company) {
            return back()
                ->with('error', 'Você não tem acesso a esta empresa.');
        }

        // Salvar empresa selecionada na sessão
        Session::put('selected_tenant_id', $company->uuid);
        Session::put('selected_tenant', $company);

        // Atualizar último login
        $clientUser->update([
            'last_login_at' => now(),
        ]);

        return redirect('/client/dashboard')
            ->with('success', 'Empresa selecionada com sucesso!');
    }

    /**
     * Processar login do cliente (CPF + Senha, SEM empresa).
     * Retorna status de autenticação e lista de empresas disponíveis.
     */
    public function login(Request $request)
    {
        $request->validate([
            'document_number' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // Buscar usuário cliente pelo documento (CPF/CNPJ)
        $clientUser = ClientUser::where('document_number', $request->document_number)
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
                ->withInput($request->only('document_number'))
                ->with('error', 'CPF/CNPJ ou senha inválidos.');
        }

        // Verificar se a conta está bloqueada
        if ($clientUser->locked_until && now()->lt($clientUser->locked_until)) {
            return back()
                ->withInput($request->only('document_number'))
                ->with('error', 'Conta temporariamente bloqueada. Tente novamente mais tarde.');
        }

        // Verificar se tem empresas acessíveis
        $companies = $clientUser->companies()
            ->where('client_company.is_active', true)
            ->get(['companies.id', 'companies.uuid', 'companies.name']);

        if ($companies->isEmpty()) {
            return back()
                ->withInput($request->only('document_number'))
                ->with('error', 'Nenhuma empresa disponível para este cliente.');
        }

        // Resetar tentativas
        $clientUser->update([
            'login_attempts' => 0,
            'locked_until' => null,
        ]);

        // Logar o cliente (sem tenant por enquanto)
        Auth::guard('client')->login($clientUser);

        // Se apenas 1 empresa, selecionar automaticamente
        if ($companies->count() === 1) {
            $company = $companies->first();
            return $this->selectCompany($company->uuid);
        }

        // Múltiplas empresas: mostrar modal de seleção
        Session::put('client_authenticated', true);
        Session::put('client_companies', $companies);

        return redirect('/client/dashboard')
            ->with('show_company_selection', true);
    }

    /**
     * Mostrar página de seleção de empresa após autenticação.
     */
    public function showCompanySelection()
    {
        $clientUser = Auth::guard('client')->user();

        if (! $clientUser) {
            return redirect('/client/login');
        }

        // Buscar empresas acessíveis
        $companies = $clientUser->companies()
            ->where('client_company.is_active', true)
            ->get();

        if ($companies->isEmpty()) {
            return redirect('/client/login')
                ->with('error', 'Nenhuma empresa disponível para este cliente.');
        }

        // Se apenas 1 empresa, selecionar automaticamente
        if ($companies->count() === 1) {
            return $this->selectCompany($companies->first()->uuid);
        }

        return view('client.select-company', compact('companies'));
    }

    /**
     * Fazer logout do cliente.
     */
    public function logout()
    {
        Auth::guard('client')->logout();
        Session::forget('selected_tenant_id');
        Session::forget('selected_tenant');

        return redirect('/client/login')
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

        return redirect('/client-admin/login')
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
