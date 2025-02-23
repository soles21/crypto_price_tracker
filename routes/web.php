<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Events\CryptoPricesUpdated;

Route::get('/', function () {
    return view('layouts.app');
});


Route::get('/test-broadcast', function () {
    $prices = [
        'BTC' => [
            'binance' => [
                'symbol' => 'BTC',
                'price' => 50000,
                'high' => 51000,
                'low' => 49000,
                'volume' => 12345,
                'priceChange' => 500,
                'priceChangePercent' => 1.01,
            ],
        ],
        'ETH' => [
            'binance' => [
                'symbol' => 'ETH',
                'price' => 4000,
                'high' => 4050,
                'low' => 3950,
                'volume' => 6789,
                'priceChange' => 30,
                'priceChangePercent' => 0.75,
            ],
        ],
    ];

    Log::info('About to dispatch CryptoPricesUpdated event');
    event(new CryptoPricesUpdated($prices));
    Log::info('Event dispatched');
    
    return response()->json([
        'status' => 'success',
        'message' => 'Event dispatched!',
        'timestamp' => now()->toIso8601String()
    ]);
});
