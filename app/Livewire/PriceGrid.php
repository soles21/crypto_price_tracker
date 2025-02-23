<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class PriceGrid extends Component
{
    public $prices = [];
    public $loading = true;
    public $error = null;
    public $lastUpdated = null;

    // Polling method
    public function pollPrices()
    {
        $this->fetchPrices();
    }

    // WebSocket event handler
    #[On('prices-updated')]
    public function handleWebSocketUpdate($data)
    {
        try {
            Log::info('WebSocket price update received', [
                'data_size' => strlen(json_encode($data)),
                'timestamp' => $data['timestamp'] ?? null
            ]);

            if (isset($data['prices']) && is_array($data['prices'])) {
                $processedPrices = [];
                
                foreach ($data['prices'] as $symbol => $exchanges) {
                    foreach ($exchanges as $exchangeName => $priceData) {
                        $processedPrices[] = [
                            'symbol' => $symbol,
                            'average_price' => $priceData['price'] ?? 0,
                            'high_price' => $priceData['high'] ?? 0,
                            'low_price' => $priceData['low'] ?? 0,
                            'price_change' => $priceData['priceChange'] ?? 0,
                            'price_change_percent' => $priceData['priceChangePercent'] ?? 0,
                            'volume' => $priceData['volume'] ?? 0,
                            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
                        ];
                    }
                }

                $this->prices = $processedPrices;
                $this->lastUpdated = now()->toDateTimeString();
                $this->error = null;

                Log::info('WebSocket price update processed', [
                    'pair_count' => count($this->prices),
                    'update_time' => $this->lastUpdated
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing WebSocket price update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function mount()
    {
        $this->fetchPrices();
    }

    public function fetchPrices()
    {
        try {
            $startTime = microtime(true);
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->get(route('api.prices.current'));
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            if ($response->successful()) {
                $data = $response->json();
                
                if (is_array($data)) {
                    $this->prices = $this->processPrices($data);
                    $this->lastUpdated = now()->toDateTimeString();
                    $this->error = null;
                    Log::info('Price grid fetch successful', [
                        'pair_count' => count($this->prices),
                        'response_time_ms' => $responseTime
                    ]);
                } else {
                    throw new \Exception('Invalid response format');
                }
            } else {
                $this->handleErrorResponse($response);
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        } finally {
            $this->loading = false;
        }
    }

    protected function processPrices(array $prices): array
    {
        return array_map(function ($price) {
            return [
                'symbol' => $price['symbol'] ?? '',
                'average_price' => $price['price']['average_price'] ?? 0,
                'high_price' => $price['price']['high_price'] ?? 0,
                'low_price' => $price['price']['low_price'] ?? 0,
                'price_change' => $price['price']['price_change'] ?? 0,
                'price_change_percent' => $price['price']['price_change_percent'] ?? 0,
                'volume' => $price['volume'] ?? 0,
                'timestamp' => $price['timestamp']['iso'] ?? now()->toIso8601String()
            ];
        }, $prices);
    }

    protected function handleErrorResponse($response)
    {
        $statusCode = $response->status();
        $errorMessage = $response->body();
        $this->error = match($statusCode) {
            404 => 'Price service not found',
            500 => 'Internal server error',
            503 => 'Price service unavailable',
            default => 'Failed to fetch prices'
        };
        Log::error('Price fetch error', [
            'status_code' => $statusCode,
            'error_message' => $errorMessage
        ]);
    }

    protected function handleException(\Exception $e)
    {
        $this->error = 'Error connecting to price service';
        
        Log::error('Price fetch exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    public function render()
    {
        return view('livewire.price-grid');
    }
}