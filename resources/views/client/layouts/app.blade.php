<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal do Cliente - {{ config('app.name') }}</title>
    @vite('resources/js/app.js')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                    success: '#10b981',
                    warning: '#f59e0b',
                    danger: '#ef4444',
                    gray: {
                        50: '#f9fafb',
                        100: '#f3f4f6',
                        200: '#e5e7eb',
                        300: '#d1d5db',
                        400: '#9ca3af',
                        500: '#6b7280',
                        600: '#4b5563',
                        700: '#374151',
                        800: '#1f2937',
                        900: '#111827',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    @auth('client')
        <!-- Header para usuários logados -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center space-x-4">
                        <!-- Logo da Empresa -->
                        @if(auth('client')->company && auth('client')->company->logo)
                            <img src="{{ asset('storage/' . auth('client')->company->logo) }}" 
                                 alt="{{ auth('client')->company->name }}" 
                                 class="h-8 w-auto rounded">
                        @else
                            <div class="flex items-center">
                                <div class="h-8 w-8 bg-primary-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-lg">
                                        {{ substr(auth('client')->client->name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ auth('client')->company->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Bem vindo de volta!
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Nome do Cliente -->
                        <span class="text-sm font-medium text-gray-700">
                            Olá, {{ auth('client')->client->name }}!
                        </span>

                        <!-- Sino de Notificações -->
                        <div class="relative">
                            <button onclick="toggleNotifications()" class="p-2 rounded-lg hover:bg-gray-100 relative">
                                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.41-1.41-10.59-10.59a2 2 0 0 0-2.828 0 4h2.828a2 2 0 0 0 2.828 0 4h-2.828zM12 2h5a2 2 0 0 1 2.828 0 4-2.828 0 4H12v4z" />
                                </svg>
                                @if(auth('client')->unreadNotifications()->count() > 0)
                                <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full animate-pulse"></span>
                                @endif
                            </button>
                        </div>

                        <!-- Menu Mobile -->
                        <div class="md:hidden">
                            <button onclick="toggleMobileMenu()" class="p-2 rounded-lg hover:bg-gray-100">
                                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Botão Sair (Desktop) -->
                        <form action="{{ route('client.logout') }}" method="POST" class="hidden md:block">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Menu Mobile (Hidden by default) -->
        <div id="mobileMenu" class="hidden fixed inset-0 z-50 md:hidden">
            <div class="fixed inset-0 bg-black bg-opacity-25" onclick="toggleMobileMenu()"></div>
            <div class="fixed right-0 top-0 h-full w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out">
                <div class="p-6 space-y-4">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <div class="text-base font-medium text-gray-900">{{ auth('client')->client->name }}</div>
                        <div class="text-sm text-gray-500">{{ auth('client')->company->name }}</div>
                    </div>
                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                        📊 Dashboard
                    </a>
                    <a href="{{ route('client.fiados') }}" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                        💰 Meus Fiados
                    </a>
                    <a href="{{ route('client.transacoes') }}" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                        📈 Transações
                    </a>
                    <a href="{{ route('client.pagamentos') }}" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                        📅 Pagamentos
                    </a>
                    <a href="{{ route('client.perfil') }}" class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                        👤 Meu Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Dropdown de Notificações (Hidden by default) -->
        <div id="notificationDropdown" class="hidden fixed right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Notificações</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <!-- Notificações serão inseridas aqui via JavaScript -->
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <main>
            @yield('content')
        </main>
    @else
        @yield('content')
    @endauth

    <script>
        // Funções para notificações
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Fechar notificações clicando fora
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const notificationButton = event.target.closest('button[onclick="toggleNotifications()"]');
            
            if (!dropdown.contains(event.target) && !notificationButton) {
                dropdown.classList.add('hidden');
            }
        });

        // Função para menu mobile
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>