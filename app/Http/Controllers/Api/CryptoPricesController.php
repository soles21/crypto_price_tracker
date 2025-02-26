<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CryptoPricesController extends Controller
{
    /**
     * Get all current crypto prices.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $prices = CryptoPrice::all()
            ->map(function (CryptoPrice $price) {
                return [
                    'id' => $price->id,
                    'pair' => $price->pair,
                    'price' => (float) $price->price,
                    'previous_price' => (float) $price->previous_price,
                    'price_change' => (float) $price->price_change,
                    'price_change_percentage' => (float) $price->price_change_percentage,
                    'exchanges' => $price->exchanges,
                    'is_increasing' => $price->isPriceIncreasing(),
                    'updated_at' => $price->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $prices,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get a specific crypto price.
     *
     * @param string $pair
     *
     * @return JsonResponse
     */
    public function show(string $pair): JsonResponse
    {
        $price = CryptoPrice::where('pair', $pair)->first();
        
        if (!$price) {
            return response()->json([
                'status' => 'error',
                'message' => "No price data found for {$pair}",
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $price->id,
                'pair' => $price->pair,
                'price' => (float) $price->price,
                'previous_price' => (float) $price->previous_price,
                'price_change' => (float) $price->price_change,
                'price_change_percentage' => (float) $price->price_change_percentage,
                'exchanges' => $price->exchanges,
                'exchange_prices' => $price->exchange_prices,
                'is_increasing' => $price->isPriceIncreasing(),
                'updated_at' => $price->updated_at->toIso8601String(),
            ],
        ]);
    }
}