<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
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

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'index']);

Route::post('/login', [LoginController::class, "authenticate"]);

Route::post('/logout', [LogoutController::class, "logout"]);

Route::get("/dashboard", [DashboardController::class, 'index']);
