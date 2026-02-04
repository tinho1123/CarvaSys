<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\PasswordRecoveryController as ClientPasswordRecoveryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', fn () => redirect('/admin/login'));

// Rotas do Admin (existente)
Route::get('/login', [LoginController::class, 'index'])->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate'])->middleware('guest');
Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth');

// Rotas do Portal do Cliente
Route::prefix('client')->name('client.')->group(function () {
    // Autenticação
    Route::get('/login', [ClientAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [ClientAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [ClientAuthController::class, 'logout'])->name('logout');
    
    // Seleção de empresa (após autenticação)
    Route::get('/select-company', [ClientAuthController::class, 'showCompanySelection'])
        ->middleware('auth:client')
        ->name('select.company.form');
    Route::post('/select-company/{companyUuid}', [ClientAuthController::class, 'selectCompany'])
        ->middleware('auth:client')
        ->name('select.company');
    
    // Recuperação de senha
    Route::get('/esqueci-senha', [ClientPasswordRecoveryController::class, 'showRecoveryForm'])->name('password.recovery');
    Route::post('/esqueci-senha', [ClientPasswordRecoveryController::class, 'sendRecoveryToken'])->name('password.recovery.post');
    Route::get('/redefinir-senha/{token}', [ClientPasswordRecoveryController::class, 'showResetForm'])->name('password.reset');
    Route::post('/redefinir-senha', [ClientPasswordRecoveryController::class, 'resetPassword'])->name('password.reset.post');

    // Primeiro Acesso
    Route::get('/primeiro-acesso', [ClientAuthController::class, 'showFirstAccessForm'])->name('first.access');
    Route::post('/primeiro-acesso', [ClientAuthController::class, 'firstAccess'])->name('first.access.post');

    // API interna para busca de empresas
    Route::get('/auth/companies', [ClientAuthController::class, 'getCompanies'])->name('auth.companies');

    // Dashboard e Portal (protegido)
    Route::middleware(['web', 'client.auth'])->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/fiados', [ClientDashboardController::class, 'fiados'])->name('fiados');
        Route::get('/transacoes', [ClientDashboardController::class, 'transacoes'])->name('transacoes');
        Route::get('/pagamentos', [ClientDashboardController::class, 'pagamentos'])->name('pagamentos');
        Route::get('/notificacoes', [ClientDashboardController::class, 'notificacoes'])->name('notificacoes');
        Route::get('/perfil', [ClientDashboardController::class, 'perfil'])->name('perfil');

        // API de notificações
        Route::post('/notificacoes/{id}/lida', [ClientDashboardController::class, 'markNotificationAsRead'])->name('notifications.read');
        Route::post('/notificacoes/marcar-todas-lidas', [ClientDashboardController::class, 'markAllNotificationsAsRead'])->name('notifications.read.all');
    });
});
