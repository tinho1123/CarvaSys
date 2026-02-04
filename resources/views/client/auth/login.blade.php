@extends('client.layouts.auth')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900">CarvaSys</h1>
            <p class="mt-2 text-sm text-gray-600">Portal do Cliente</p>
        </div>

        <!-- Card de Login -->
        <div class="bg-white py-8 px-6 shadow rounded-lg">
            <!-- Mensagens -->
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="text-sm text-red-600">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                    <div class="text-sm text-green-600">{{ session('success') }}</div>
                </div>
            @endif

            <!-- Formulário -->
            <form class="space-y-6" action="{{ route('client.login') }}" method="POST">
                @csrf

                <!-- CPF/CNPJ -->
                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700">
                        CPF ou CNPJ
                    </label>
                    <input 
                        type="text" 
                        id="document_number" 
                        name="document_number"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('document_number') border-red-500 @enderror"
                        placeholder="999.999.999-99"
                        value="{{ old('document_number') }}"
                        required
                        autofocus
                    >
                    @error('document_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Senha
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                        placeholder="Sua senha"
                        required
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botão de Login -->
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Entrar
                </button>
            </form>

            <!-- Links de Ajuda -->
            <div class="mt-6 space-y-3 text-sm text-center">
                <div>
                    <a href="{{ route('client.password.recovery') }}" class="text-blue-600 hover:text-blue-500">
                        Esqueci minha senha
                    </a>
                </div>
                <div class="text-gray-600">
                    Primeiro acesso?
                    <a href="{{ route('client.first.access') }}" class="text-blue-600 hover:text-blue-500">
                        Criar acesso
                    </a>
                </div>
            </div>
        </div>

        <!-- Informação Adicional -->
        <div class="text-center text-xs text-gray-500">
            <p>Versão Beta • © 2026 CarvaSys</p>
        </div>
    </div>
</div>

<script>
// Máscara para CPF/CNPJ
document.getElementById('document_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length <= 11) {
        // CPF: 999.999.999-99
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else {
        // CNPJ: 99.999.999/9999-99
        value = value.slice(0, 14).replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }
    
    e.target.value = value;
});
</script>
@endsection