<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CryptoPricesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prices;
    private $timestamp;

    public function __construct(array $prices)
    {
        try {
            $this->prices = $this->sanitizePrices($prices);
            $this->timestamp = Carbon::now();
        } catch (\Exception $e) {
            Log::error('Failed to create CryptoPricesUpdated event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input_prices_count' => count($prices)
            ]);
            
            $this->prices = [];
            $this->timestamp = Carbon::now();
        }
    }

    protected function sanitizePrices(array $prices): array
    {
        return array_filter(array_map(function ($exchangePrices) {
            return array_filter(array_map(function ($priceData) {
                if (!$this->isValidPriceData($priceData)) {
                    return null;
                }

                return array_intersect_key($priceData, array_flip([
                    'symbol', 'price', 'high', 'low', 
                    'volume', 'priceChange', 'priceChangePercent'
                ]));
            }, $exchangePrices));
        }, $prices));
    }

    protected function isValidPriceData(array $priceData): bool
    {
        return isset($priceData['symbol']) 
            && isset($priceData['price']) 
            && is_numeric($priceData['price']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('crypto-prices');
    }

    public function broadcastAs(): string
    {
        return 'prices.updated';
    }

    public function broadcastWith(): array
    {
        try {
            return [
                'prices' => $this->prices,
                'timestamp' => $this->timestamp->toIso8601String(),
                'meta' => $this->generateMetadata()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate broadcast data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'prices_count' => count($this->prices)
            ]);

            return [
                'prices' => [],
                'timestamp' => $this->timestamp->toIso8601String(),
                'meta' => [
                    'error' => 'Failed to generate broadcast data'
                ]
            ];
        }
    }

    protected function generateMetadata(): array
    {
        try {
            $exchangeStats = $this->calculateExchangeStats();
            return [
                'exchange_count' => count($this->prices),
                'total_pairs' => $exchangeStats['total_pairs'],
                'server_time' => $this->timestamp->toIso8601String(),
                'exchanges' => $exchangeStats['exchanges'],
                'price_range' => $exchangeStats['price_range']
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate metadata', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'exchange_count' => 0,
                'total_pairs' => 0,
                'server_time' => $this->timestamp->toIso8601String(),
                'exchanges' => [],
                'price_range' => ['min' => null, 'max' => null]
            ];
        }
    }

    protected function calculateExchangeStats(): array
    {
        try {
            $exchanges = [];
            $allPrices = [];

            foreach ($this->prices as $symbol => $symbolPrices) {
                foreach ($symbolPrices as $exchange => $priceData) {
                    $exchanges[$exchange] = ($exchanges[$exchange] ?? 0) + 1;
                    $allPrices[] = $priceData['price'];
                }
            }

            return [
                'total_pairs' => count($this->prices),
                'exchanges' => $exchanges,
                'price_range' => [
                    'min' => count($allPrices) > 0 ? min($allPrices) : null,
                    'max' => count($allPrices) > 0 ? max($allPrices) : null
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate exchange stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'prices_count' => count($this->prices)
            ]);

            return [
                'total_pairs' => 0,
                'exchanges' => [],
                'price_range' => ['min' => null, 'max' => null]
            ];
        }
    }
}