@extends('client.layouts.auth')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo da Empresa -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Portal do Cliente</h1>
            <p class="mt-2 text-sm text-gray-600">Acesse suas informações de fiados e transações</p>
        </div>

        <!-- Formulário de Login -->
        <div class="bg-white py-8 px-6 shadow rounded-lg">
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border border-red-200 rounded-md">
                    <div class="text-sm text-red-600">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border border-green-200 rounded-md">
                    <div class="text-sm text-green-600">{{ session('success') }}</div>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('client.login') }}" method="POST">
                @csrf
                <input type="hidden" name="company_id" id="selectedCompanyId" value="{{ session('selected_company', '') }}">
                
                <!-- Seleção de Empresa -->
                <div>
                    <label for="companySearch" class="block text-sm font-medium text-gray-700">Selecione sua Empresa</label>
                    <div class="relative mt-1">
                        <input type="text" 
                               id="companySearch" 
                               name="company_search" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Buscar empresa..."
                               onkeyup="searchCompanies(this.value)">
                        
                        <!-- Lista de Empresas -->
                        <div id="companyList" class="hidden absolute z-10 mt-1 w-full bg-white rounded-lg shadow-lg max-h-60 overflow-auto">
                            <div class="p-2">
                                <div class="space-y-2">
                                    <!-- Empresas serão carregadas aqui via JavaScript -->
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="company_display" id="companyDisplay" readonly 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-pointer"
                               placeholder="Selecione uma empresa..."
                               onclick="document.getElementById('companyList').classList.toggle('hidden')">
                    </div>
                </div>

                <!-- Campos de Login -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                           placeholder="seu@email.com"
                           value="{{ old('email') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                           placeholder="Digite sua senha">
                </div>

                <!-- Opções -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">Lembrar-me</label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="{{ route('client.password.recovery') }}" class="font-medium text-primary-600 hover:text-primary-500">
                            Esqueci minha senha
                        </a>
                    </div>
                </div>

                <!-- Botão de Login -->
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-primary-500 group-hover:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 012 2v2a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 012-2H2a2 2 0 01-2 2v2a2 2 0 012-2h12a2 2 0 012-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 3a1.5 1.5 0 003 0v2.586c0 .207.415.36.414.364.364.991.893l1.414 1.414c.926.926 2.082 2.067 2.893 2.484.2.484-.207-.415-.364-.414-.364-.91-.893L2.5 3V5.586c0 .893.415.415 1.784.926.364.207.415.207-.893.893-.5.5.5.5h1a1.5 1.5 0 003 0z" />
                            </svg>
                        </span>
                        Entrar
                    </button>
                </div>
            </form>

            <!-- Primeiro Acesso -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Ainda não tem acesso?
                </p>
                <a href="{{ route('client.first.access') }}" class="font-medium text-primary-600 hover:text-primary-500">
                    Criar meu primeiro acesso
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Busca de empresas
let searchTimeout;
let companies = [];

function searchCompanies(query) {
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        document.getElementById('companyList').classList.add('hidden');
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`/cliente/auth/companies?search=${query}`)
            .then(response => response.json())
            .then(data => {
                companies = data;
                displayCompanyList(data);
            });
    }, 300);
}

function displayCompanyList(companyData) {
    const list = document.getElementById('companyList');
    const companyItems = list.querySelector('.space-y-2');
    
    if (companyData.length === 0) {
        companyItems.innerHTML = '<div class="text-sm text-gray-500">Nenhuma empresa encontrada</div>';
        list.parentElement.classList.remove('hidden');
        return;
    }

    companyItems.innerHTML = companyData.map(company => `
        <div class="p-2 hover:bg-gray-50 cursor-pointer rounded-lg transition-colors duration-200" 
             onclick="selectCompany('${company.uuid}', '${company.name}', '${company.logo || ''}')">
            <div class="flex items-center space-x-3">
                ${company.logo ? 
                    `<img src="/storage/${company.logo}" alt="${company.name}" class="h-8 w-8 rounded">` :
                    `<div class="h-8 w-8 bg-primary-100 rounded-full flex items-center justify-center">
                        <span class="text-primary-600 font-bold text-sm">${company.name.charAt(0)}</span>
                    </div>`
                }
                <div>
                    <div class="text-sm font-medium text-gray-900">${company.name}</div>
                </div>
            </div>
        </div>
    `).join('');
    
    list.parentElement.classList.remove('hidden');
}

function selectCompany(uuid, name, logo) {
    document.getElementById('selectedCompanyId').value = uuid;
    const displayInput = document.getElementById('companyDisplay');
    
    if (logo) {
        displayInput.innerHTML = `<div class="flex items-center space-x-3">
            <img src="/storage/${logo}" alt="${name}" class="h-8 w-8 rounded">
            <div>
                <div class="text-sm font-medium text-gray-900">${name}</div>
            </div>
        </div>`;
    } else {
        displayInput.innerHTML = name;
    }
    
    document.getElementById('companyList').classList.add('hidden');
}

// Fechar dropdown clicando fora
document.addEventListener('click', function(event) {
    const searchInput = document.getElementById('companySearch');
    const list = document.getElementById('companyList');
    
    if (!searchInput.contains(event.target) && !list.contains(event.target)) {
        list.classList.add('hidden');
    }
});
</script>
@endsection