<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PasswordRecoveryController extends Controller
{
    /**
     * Mostrar formulário de recuperação de senha.
     */
    public function showRecoveryForm()
    {
        return view('client.auth.password-recovery');
    }

    /**
     * Processar solicitação de recuperação.
     */
    public function sendRecoveryToken(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:cpf,cnpj',
            'document_number' => 'required|string',
            'email' => 'required|email',
            'company_id' => 'required|uuid|exists:companies,uuid',
        ]);

        // Buscar cliente pelo documento e email
        $client = Client::where([
            'document_number' => $request->document_number,
            'email' => $request->email,
        ])->first();

        if (! $client) {
            return back()
                ->withInput()
                ->with('error', 'Cliente não encontrado com os dados informados.');
        }

        // Verificar se o cliente possui usuário
        if (! $client->user) {
            return back()
                ->withInput()
                ->with('error', 'Cliente não possui acesso criado. Utilize a opção "Primeiro Acesso".');
        }

        // Gerar token de recuperação
        $token = Password::createToken($client->user);

        // TODO: Enviar email com o token
        // Mail::to($client->email)->send(new PasswordRecoveryMail($token));

        return back()
            ->with('success', 'Instruções de recuperação de senha foram enviadas para seu e-mail.');
    }

    /**
     * Mostrar formulário de reset de senha.
     */
    public function showResetForm($token)
    {
        return view('client.auth.reset-password', ['token' => $token]);
    }

    /**
     * Processar reset de senha.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verificar token
        $user = ClientUser::where('email', $request->email)
            ->first();

        if (! $user || ! Password::tokenExists($user, $request->token)) {
            return back()
                ->withInput()
                ->with('error', 'Token inválido ou expirado.');
        }

        // Resetar senha
        $user->password = Hash::make($request->password);
        $user->remember_token = null;
        $user->save();

        // Remover todos os tokens de reset
        Password::deleteToken($user);

        return redirect('/cliente/login')
            ->with('success', 'Senha redefinida com sucesso! Faça login para continuar.');
    }
}
