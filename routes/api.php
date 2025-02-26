<?php

use App\Http\Controllers\Api\CryptoPricesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(function () {
    // Crypto prices API
    Route::get('/prices', [CryptoPricesController::class, 'index']);
    Route::get('/prices/{pair}', [CryptoPricesController::class, 'show']);
});