<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoPair;
use App\Models\PriceAggregate;
use App\Models\CryptoExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CryptoPriceController extends Controller
{
    private const CACHE_PERIODS = [
        '1h' => 5,   // 5 minutes cache for 1-hour data
        '24h' => 30, // 30 minutes cache for 24-hour data
        '7d' => 60,  // 1-hour cache for 7-day data
        '30d' => 120 // 2-hour cache for 30-day data
    ];

    public function getCurrentPrices()
    {
        try {
            return Cache::remember('current_prices', 5, function () {
                return PriceAggregate::with(['pair'])
                    ->whereIn('id', function ($query) {
                        $query->selectRaw('MAX(id)')
                            ->from('price_aggregates')
                            ->groupBy('pair_id');
                    })
                    ->get()
                    ->map(function ($aggregate) {
                        return $this->formatPriceAggregate($aggregate);
                    });
            });
        } catch (\Exception $e) {
            Log::error('Failed to fetch current prices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Unable to fetch current prices',
                'error' => true
            ], 500);
        }
    }

    public function getPairHistory(Request $request, string $symbol)
    {
        try {
            $request->validate([
                'period' => 'string|in:1h,24h,7d,30d',
            ]);

            $pair = CryptoPair::where('symbol', strtoupper($symbol))->firstOrFail();
            
            $period = $request->get('period', '24h');
            $cacheTime = self::CACHE_PERIODS[$period];

            return Cache::remember("pair_history_{$symbol}_{$period}", $cacheTime, function () use ($pair, $period) {
                $since = match($period) {
                    '1h' => now()->subHour(),
                    '24h' => now()->subDay(),
                    '7d' => now()->subDays(7),
                    '30d' => now()->subDays(30),
                };

                return PriceAggregate::where('pair_id', $pair->id)
                    ->where('calculated_at', '>=', $since)
                    ->orderBy('calculated_at', 'asc')
                    ->get()
                    ->map(function ($aggregate) {
                        return $this->formatPriceHistoryItem($aggregate);
                    });
            });
        } catch (\Exception $e) {
            Log::error('Failed to fetch pair history', [
                'symbol' => $symbol,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Unable to fetch pair history',
                'error' => true
            ], 500);
        }
    }

    public function getExchangeStatus()
    {
        try {
            return Cache::remember('exchange_status', 30, function () {
                return CryptoExchange::select(['name', 'is_active', 'last_successful_fetch_at'])
                    ->get()
                    ->map(function ($exchange) {
                        return $this->formatExchangeStatus($exchange);
                    });
            });
        } catch (\Exception $e) {
            Log::error('Failed to fetch exchange status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Unable to fetch exchange status',
                'error' => true
            ], 500);
        }
    }

    private function formatPriceAggregate($aggregate)
    {
        return [
            'symbol' => $aggregate->pair->symbol,
            'price' => [
                'average_price' => round($aggregate->average_price, 8),
                'high_price' => round($aggregate->high_price, 8),
                'low_price' => round($aggregate->low_price, 8),
                'price_change' => round($aggregate->price_change, 8),
                'price_change_percent' => round($aggregate->price_change_percent, 2)
            ],
            'volume' => round($aggregate->volume, 4),
            'exchange_data' => $aggregate->exchange_data,
            'sources' => [
                'exchanges' => $aggregate->exchange_ids,
                'count' => $aggregate->number_of_sources
            ],
            'timestamp' => [
                'iso' => $aggregate->calculated_at->toIso8601String(),
                'unix' => $aggregate->calculated_at->timestamp * 1000
            ]
        ];
    }

    private function formatPriceHistoryItem($aggregate)
    {
        return [
            'price' => round($aggregate->average_price, 8),
            'high' => round($aggregate->high_price, 8),
            'low' => round($aggregate->low_price, 8),
            'volume' => round($aggregate->volume, 4),
            'change' => round($aggregate->price_change, 8),
            'change_percent' => round($aggregate->price_change_percent, 2),
            'exchange_data' => $aggregate->exchange_data,
            'timestamp' => $aggregate->calculated_at->timestamp * 1000
        ];
    }

    private function formatExchangeStatus($exchange)
    {
        return [
            'name' => $exchange->name,
            'status' => $exchange->is_active,
            'last_update' => $exchange->last_successful_fetch_at?->diffForHumans(),
            'health' => $exchange->getHealthStatus(),
            'last_update_timestamp' => $exchange->last_successful_fetch_at?->timestamp * 1000
        ];
    }
}