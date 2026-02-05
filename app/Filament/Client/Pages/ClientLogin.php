<?php

namespace App\Filament\Client\Pages;

use App\Models\Client;
use App\Models\Company;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClientLogin extends BaseLogin implements HasActions
{
    use InteractsWithActions;

    public array $companies = [];

    /**
     * Ação de seleção de empresa (Modal)
     */
    public function selectCompanyAction(): Action
    {
        return Action::make('selectCompany')
            ->label('Selecionar Empresa')
            ->modalHeading('Selecione a Empresa')
            ->modalSubmitActionLabel('Acessar')
            ->form([
                Select::make('company_uuid')
                    ->label('Empresa')
                    ->options(fn () => collect($this->companies)->pluck('name', 'uuid'))
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data) {
                return $this->redirectToTenant($data['company_uuid']);
            })
            ->closeModalByClickingAway(false);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('document_number')
                    ->label('CPF/CNPJ')
                    ->required()
                    ->mask('999.999.999-99')
                    ->placeholder('000.000.000-00')
                    ->autocomplete('off')
                    ->autofocus(),

                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->statePath('data'); // OBRIGATÓRIO para o template simple do Filament
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.document_number' => __('filament-panels::pages/auth/login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();
        $document = preg_replace('/[^0-9]/', '', $data['document_number']);

        $client = Client::where('document_number', $document)->first();

        if (! $client) {
            throw ValidationException::withMessages([
                'data.document_number' => 'CPF/CNPJ não encontrado.',
            ]);
        }

        if (! $client->validateLoginAttempts()) {
            throw ValidationException::withMessages([
                'data.document_number' => 'Conta bloqueada temporariamente.',
            ]);
        }

        if (! Hash::check($data['password'], $client->password)) {
            $client->incrementLoginAttempts();
            throw ValidationException::withMessages([
                'data.document_number' => 'Credenciais inválidas.',
            ]);
        }

        if (! $client->active) {
            throw ValidationException::withMessages([
                'data.document_number' => 'Conta inativa.',
            ]);
        }

        $client->resetLoginAttempts();
        Auth::guard('client')->login($client, true);

        $companies = $client->companies()->get(['companies.uuid', 'companies.name'])->toArray();

        if (empty($companies)) {
            Auth::guard('client')->logout();
            throw ValidationException::withMessages([
                'data.document_number' => 'Sem acesso a empresas.',
            ]);
        }

        if (count($companies) === 1) {
            return $this->redirectToTenant($companies[0]['uuid']);
        }

        // Múltiplas empresas: Abrir Modal
        $this->companies = $companies;
        $this->mountAction('selectCompany');

        return null;
    }

    protected function redirectToTenant(string $tenantUuid): LoginResponse
    {
        $tenant = Company::where('uuid', $tenantUuid)->firstOrFail();
        session()->put('selected_tenant_id', $tenant->id);

        return app(LoginResponse::class);
    }

    protected function getAuthGuard(): string
    {
        return 'client';
    }
}
