@extends('client.layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Cards de Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total em Fiados -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.242-.195.52-.195.918 0C10.465 1.255 11.255 8.418c0 .483-.393.964-.393 1.918 0 1.824 1.423 2.947 2.947.482.117.965 1.965.0c.026-.024.065-.067.286-.065.353-.067.53-.067.82-.067.744-.466.402.364-.965.364.965.744.393.819.393.819.744.176 1.644.176 1.644-.962.403.962.403 1.644.652.653-1.484-.195-.1 1.484.653-.819.364-.965-.819.744-.195-1.484.653-.819.364.965z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total em Fiados</dt>
                                <dd class="text-lg font-medium text-gray-900">R$ {{ number_format($totalFiados, 2, ',', '.') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagamentos Próximos -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v10a1 1 0 001 1h2a1 1 0 001 1V3a1 1 0 00-1-1zM4 13a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001 1V3a1 1 0 00-1-1zm10 10V3a1 1 0 00-1-1h2a1 1 0 001 1v10a1 1 0 001-1h-2a1 1 0 01-1-1V3a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pagamentos Próximos</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $proximosPagamentos }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transações do Mês -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 011-1h2a1 1 0 011-1v-2a1 1 0 011-1zM11 5h6a1 1 0 110 2v8a1 1 0 110-2h-6a1 1 0 110-2V7a1 1 0 011-1zm1 1a1 1 0 100 2h8a1 1 0 100 2v2a1 1 0 100-2h-8a1 1 0 100-2V7a1 1 0 100-2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Transações do Mês</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $transacoesMes }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notificações Não Lidas -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a6 6 0 00-6 6v1a1 1 0 001 1h3a1 1 0 001 1v3a1 1 0 001 1h1a1 1 0 001 1v3a1 1 0 001 1h3a1 1 0 001-1V7a6 6 0 0012 12zM10 18a8 8 0 100-16v4a8 8 0 100 16v4a8 8 0 100-16zm0-14a4 4 0 00-8 8v4a4 4 0 008 8v4a4 4 0 008-8z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Notificações</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $notificacoesNaoLidas }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Actions -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Ações Rápidas</h3>
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <a href="{{ route('client.fiados') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Ver Meus Fiados
                        </a>
                        
                        <a href="{{ route('client.transacoes') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Histórico de Transações
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas Atividades -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Minhas Atividades</h3>
                <div class="mt-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-4">
                                    <div class="relative flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <span class="text-yellow-600 font-medium text-sm">1</span>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm text-gray-900">
                                                <strong>Fiado criado:</strong> Novo fiado no valor de R$ 250,00 para João Silva.
                                            </div>
                                            <div class="text-xs text-gray-500">Há 2 horas</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative pb-4">
                                    <div class="relative flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <span class="text-green-600 font-medium text-sm">2</span>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm text-gray-900">
                                                <strong>Pagamento registrado:</strong> Pagamento de R$ 100,00 recebido de Maria Santos.
                                            </div>
                                            <div class="text-xs text-gray-500">Há 1 dia</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative pb-4">
                                    <div class="relative flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 font-medium text-sm">3</span>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm text-gray-900">
                                                <strong>Login no sistema:</strong> {{ auth('client')->client->name }} acessou o portal.
                                            </div>
                                            <div class="text-xs text-gray-500">Há 30 minutos</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection