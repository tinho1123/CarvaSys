@extends('client.layouts.auth')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Selecione sua Empresa</h1>
            <p class="mt-2 text-sm text-gray-600">Escolha qual empresa você deseja acessar hoje</p>
        </div>

        <!-- Formulário de Seleção -->
        <form class="mt-8 space-y-6 bg-white shadow-lg rounded-lg p-8" action="{{ route('client.select.company') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="select">
            
            <div class="space-y-4">
                <label for="company" class="block text-sm font-medium text-gray-700">Empresas Disponíveis:</label>
                <select id="company" name="company_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Selecione uma empresa...</option>
                    <!-- Empresas serão carregadas aqui via JavaScript -->
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-primary-500 group-hover:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2h2a2 0 002 2h2a2 0 012 2h2a2 0 002 2H9a2 2 0 00-2 2v2a2 2 0 002 2H7a2 2 0 00-2-2v2a2 2 0 002 2z" />
                        </svg>
                    </span>
                    </span>
                    Entrar na Empresa Selecionada
                </button>
            </div>
        </form>

        <!-- Alternativa: Criar Primeiro Acesso -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Ainda não tem acesso? 
                <a href="{{ route('client.first.access') }}" class="font-medium text-primary-600 hover:text-primary-500">
                    Crie seu primeiro acesso
                </a>
            </p>
        </div>
    </div>
</div>

<script>
// Buscar empresas quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    loadCompanies();
});

function loadCompanies() {
    fetch('/client/auth/companies')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('company');
            const options = data.map(company => `<option value="${company.uuid}">${company.name}</option>`);
            
            // Limpar opções existentes
            select.innerHTML = '<option value="">Selecione uma empresa...</option>';
            
            // Adicionar novas opções
            options.forEach(option => {
                select.innerHTML += option;
            });
        })
        .catch(error => {
            console.error('Erro ao carregar empresas:', error);
        });
}

// Processar seleção de empresa
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('/client/auth/companies', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/client/dashboard';
        } else {
            alert(data.message || 'Ocorreu um erro. Tente novamente.');
        }
    })
    .catch(error => {
        console.error('Erro na seleção de empresa:', error);
    });
});
</script>
@endsection