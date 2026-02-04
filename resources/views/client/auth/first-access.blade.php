@extends('client.layouts.auth')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-center text-gray-800">Primeiro Acesso</h1>
            <p class="text-center text-gray-600 mt-2">Configure sua senha inicial</p>
        </div>

        <form method="POST" action="{{ route('client.first.access.post') }}">
            @csrf

            <!-- Tipo de Documento -->
            <div class="mb-4">
                <label for="document_type" class="block text-sm font-medium text-gray-700">Tipo de Documento</label>
                <select id="document_type" name="document_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="cpf" {{ old('document_type') == 'cpf' ? 'selected' : '' }}>CPF</option>
                    <option value="cnpj" {{ old('document_type') == 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                </select>
                @error('document_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Número do Documento -->
            <div class="mb-4">
                <label for="document_number" class="block text-sm font-medium text-gray-700">CPF/CNPJ</label>
                <input id="document_number" type="text" name="document_number" value="{{ old('document_number') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('document_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Senha -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                <input id="password" type="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirmar Senha -->
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Senha</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('client.login') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Voltar ao Login</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Configurar Senha
                </button>
            </div>
        </form>
    </div>
</div>
@endsection