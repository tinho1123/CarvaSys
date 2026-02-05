<?php

use App\Http\Controllers\FavoredTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Fiados de Clientes transactions routes
    Route::prefix('favored-transactions')->name('favored-transactions.')->group(function () {
        Route::get('/', [FavoredTransactionController::class, 'index'])->name('index');
        Route::post('/', [FavoredTransactionController::class, 'store'])->name('store');
        Route::get('/clients-with-transactions', [FavoredTransactionController::class, 'getClientsWithTransactions'])->name('clients-with-transactions');
        Route::put('/{transaction}', [FavoredTransactionController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [FavoredTransactionController::class, 'destroy'])->name('destroy');
        Route::post('/{transaction}/pay', [FavoredTransactionController::class, 'payDebt'])->name('pay');
    });

});
