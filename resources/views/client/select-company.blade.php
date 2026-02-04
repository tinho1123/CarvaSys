@extends('client.layouts.auth')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900">CarvaSys</h1>
            <p class="mt-2 text-sm text-gray-600">Selecione sua empresa</p>
        </div>

        <!-- Card -->
        <div class="bg-white py-8 px-6 shadow rounded-lg">
            <!-- Mensagens -->
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="text-sm text-red-600">{{ session('error') }}</div>
                </div>
            @endif

            <!-- Instruções -->
            <div class="mb-6">
                <p class="text-sm text-gray-600">
                    Você tem acesso às seguintes empresas. Selecione uma para continuar:
                </p>
            </div>

            <!-- Lista de Empresas -->
            <div class="space-y-3">
                @forelse ($companies as $company)
                    <form action="{{ route('client.select.company', $company->uuid) }}" method="POST" class="w-full">
                        @csrf
                        <button 
                            type="submit"
                            class="w-full p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 text-left"
                        >
                            <div class="flex items-center space-x-4">
                                @if ($company->logo)
                                    <img src="/storage/{{ $company->logo }}" alt="{{ $company->name }}" class="h-12 w-12 rounded-lg object-cover">
                                @else
                                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <span class="text-blue-600 font-bold text-lg">{{ $company->name[0] }}</span>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">{{ $company->name }}</h3>
                                    @if ($company->foundation_date)
                                        <p class="text-xs text-gray-500">Desde {{ $company->foundation_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </form>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-600">Nenhuma empresa disponível</p>
                    </div>
                @endforelse
            </div>

            <!-- Link de Logout -->
            <div class="mt-6 text-center">
                <form action="{{ route('client.logout') }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Usar outro CPF/CNPJ
                    </button>
                </form>
            </div>
        </div>

        <!-- Informação Adicional -->
        <div class="text-center text-xs text-gray-500">
            <p>Versão Beta • © 2026 CarvaSys</p>
        </div>
    </div>
</div>
@endsection
