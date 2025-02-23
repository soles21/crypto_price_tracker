<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CryptoPriceController;

Route::prefix('v1')->group(function () {
    Route::get('/prices/current', [CryptoPriceController::class, 'getCurrentPrices'])
        ->name('api.prices.current');

    Route::get('/prices/{symbol}/history', [CryptoPriceController::class, 'getPairHistory'])
        ->name('api.prices.history')
        ->where('symbol', '[A-Za-z0-9]+');

    Route::get('/exchanges/status', [CryptoPriceController::class, 'getExchangeStatus'])
        ->name('api.exchanges.status');
});