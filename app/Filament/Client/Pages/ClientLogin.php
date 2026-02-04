<?php

namespace App\Filament\Client\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Facades\Filament;

class ClientLogin extends BaseLogin
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public $showModal = false;
    public $companies = [];
    public $selectedCompanyId = null;
    public $authenticatedUserId = null;

    public function getView(): string
    {
        return 'filament.client.pages.client-login';
    }

    public function mount(): void
    {
        // Clear previous tenant selection from session
        session()->forget('selected_tenant_id');

        // Don't redirect even if authenticated - let the user select company
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $schema = [
            TextInput::make('cpf')
                ->label('CPF')
                ->required()
                ->mask('999.999.999-99')
                ->validationAttribute('CPF'),

            TextInput::make('password')
                ->label('Senha')
                ->password()
                ->required(),
        ];

        return $form
            ->schema($schema)
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'cpf' => __('filament::login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        // Clean CPF for search
        $cpf = preg_replace('/[^0-9]/', '', $data['cpf']);

        // Try to authenticate with CPF
        if (! Auth::guard('client')->attempt([
            'document_number' => $cpf,
            'document_type' => 'cpf',
            'password' => $data['password'],
        ])) {
            throw ValidationException::withMessages([
                'cpf' => __('filament::login.messages.failed'),
            ]);
        }

        // Get authenticated user
        $user = Auth::guard('client')->user();

        // Check if user has associated companies
        try {
            $companies = $user->companies()->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading companies for user ' . $user->id . ': ' . $e->getMessage());
            throw ValidationException::withMessages([
                'cpf' => 'Erro interno ao carregar empresas.',
            ]);
        }

        if ($companies->isEmpty()) {
            Auth::guard('client')->logout();
            throw ValidationException::withMessages([
                'cpf' => 'Você não tem acesso a nenhuma empresa.',
            ]);
        }

        // Always show company selection - don't redirect automatically
        $this->companies = $companies->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'uuid' => $company->uuid,
        ])->toArray();

        // Store user ID for later use
        $this->authenticatedUserId = $user->id;

        // IMPORTANT: Clear tenant from session to prevent automatic redirect
        session()->forget('selected_tenant_id');

        $this->showModal = true;

        return null;
    }

    public function selectCompany(): LoginResponse
    {
        if (empty($this->selectedCompanyId)) {
            throw ValidationException::withMessages([
                'selectedCompanyId' => 'Please select a company.',
            ]);
        }

        // Find the selected company
        $company = collect($this->companies)->firstWhere('id', $this->selectedCompanyId);

        if (!$company) {
            throw ValidationException::withMessages([
                'selectedCompanyId' => 'Invalid company selected.',
            ]);
        }

        // Set tenant ANTES de retornar
        $companyModel = \App\Models\Company::find($company['id']);
        $this->setTenant($companyModel);

        // Set cookie, session, and localStorage APENAS após selecionar empresa
        session(['selected_company' => $company['uuid']]);
        $this->js("localStorage.setItem('selected_company', '{$company['uuid']}'); document.cookie = 'selected_company={$company['uuid']}; path=/; max-age=31536000';");

        // Close modal
        $this->showModal = false;

        // Log para debug
        \Illuminate\Support\Facades\Log::info('ClientLogin selectCompany', [
            'user_id' => $this->authenticatedUserId,
            'company_id' => $company['id'],
            'company_uuid' => $company['uuid'],
        ]);

        // Return custom login response that redirects to dashboard with tenant
        return app(ClientLoginResponse::class);
    }

    protected function setTenant($company): void
    {
        // Set tenant in global context
        app()->singleton('current_tenant', fn () => $company);

        // Add to session for future requests
        session(['selected_tenant_id' => $company->uuid]);
    }
}
