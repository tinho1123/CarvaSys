<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
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

use App\Http\Controllers\Marketplace\MarketplaceController;
use App\Http\Controllers\Marketplace\SSOCallbackController;

Route::get('/', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/store/{company:uuid}', [MarketplaceController::class, 'show'])->name('marketplace.show');

// Fluxo SSO e Cadastro Completo
Route::get('/sso-callback', function() { return redirect()->route('marketplace.index'); });
Route::post('/sso-callback', SSOCallbackController::class)->name('marketplace.sso-callback');
Route::get('/complete-profile', [SSOCallbackController::class, 'completeProfile'])->name('marketplace.complete-profile');
Route::post('/complete-profile', [SSOCallbackController::class, 'storeProfile'])->name('marketplace.store-profile');


// Rotas do Admin (existente)
Route::get('/login', [LoginController::class, 'index'])->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate'])->middleware('guest');
Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth');
