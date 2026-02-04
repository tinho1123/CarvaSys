<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

    <!-- Modal de Seleção de Empresa -->
    <div x-show="$wire.showModal"
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
        <div class="relative w-full max-w-md mx-4">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-2xl px-8 py-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 text-center">
                    Selecione a Empresa
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6 text-center">
                    Você tem acesso a mais de uma empresa. Selecione qual deseja acessar.
                </p>
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Empresa
                    </label>
                    <select wire:model.live="selectedCompanyId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-600 dark:bg-gray-800 dark:text-white">
                        <option value="">Selecione uma empresa</option>
                        @foreach($companies as $company)
                            <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-4">
                    <button
                        wire:click="selectCompany"
                        type="button"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Entrar
                    </button>
                    <button
                        wire:click="$set('showModal', false)"
                        type="button"
                        class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>