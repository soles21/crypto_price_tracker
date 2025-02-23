<?php

namespace App\Jobs;

use App\Models\CryptoPair;
use App\Models\CryptoExchange;
use App\Events\CryptoPricesUpdated;
use App\Models\CryptoPrice;
use App\Services\Crypto\PriceFetcher\PriceFetcherInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FetchCryptoPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;
    public $backoff = [5, 10, 20];
    private $interval;

    public function __construct()
    {
        $this->onQueue('crypto-prices');
        $this->interval = config('crypto.fetch_interval', 5);
    }

    public function handle(PriceFetcherInterface $priceFetcher)
    {
        $startTime = microtime(true);
        try {
            DB::transaction(function () {
                $this->initializeDatabase();
            });

            $prices = $this->fetchPricesWithTimeout($priceFetcher);
            $this->processPrices($prices);

        } catch (\Exception $e) {
            $this->handleJobFailure($e, $startTime);
        } finally {
            $this->dispatchNextJob($startTime);
        }
    }

    protected function processPrices(array $prices)
    {
        if (!empty($prices)) {
            try {
                $formattedPrices = [];
                foreach ($prices as $symbol => $exchanges) {
                    foreach ($exchanges as $exchange => $priceData) {
                        if (!isset($formattedPrices[$symbol])) {
                            $formattedPrices[$symbol] = [];
                        }
                        $formattedPrices[$symbol][$exchange] = [
                            'symbol' => $symbol,
                            'price' => $priceData['price'] ?? 0,
                            'high' => $priceData['high'] ?? 0,
                            'low' => $priceData['low'] ?? 0,
                            'volume' => $priceData['volume'] ?? 0,
                            'priceChange' => $priceData['priceChange'] ?? 0,
                            'priceChangePercent' => $priceData['priceChangePercent'] ?? 0
                        ];
                    }
                }

                broadcast(new CryptoPricesUpdated($formattedPrices));

            } catch (\Exception $e) {
                Log::error('Failed to process or broadcast prices', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
            }
        }
    }

    protected function fetchPricesWithTimeout(PriceFetcherInterface $priceFetcher): array
    {
        try {
            $prices = $priceFetcher->fetchAllPrices();
            return array_filter($prices, function ($prices) {
                return !empty($prices);
            });
        } catch (\Exception $e) {
            Log::error('Price fetching failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    protected function handleJobFailure(\Exception $e, float $startTime)
    {
        Log::error('Crypto price job failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'execution_time' => microtime(true) - $startTime
        ]);
        $this->fail($e);
    }

    protected function dispatchNextJob(float $startTime)
    {
        $executionTime = microtime(true) - $startTime;
        $adjustedInterval = max($this->interval - $executionTime, 1);

        try {
            static::dispatch()
                ->delay(now()->addSeconds($adjustedInterval))
                ->onQueue('crypto-prices');
        } catch (\Exception $e) {
            Log::critical('Failed to dispatch next job', [
                'error' => $e->getMessage()
            ]);
            try {
                static::dispatch()->onQueue('crypto-prices');
            } catch (\Exception $retryException) {
                Log::critical('Fallback job dispatch failed', [
                    'error' => $retryException->getMessage()
                ]);
            }
        }
    }

    protected function initializeDatabase()
    {
        if (CryptoPair::count() === 0) {
            $pairs = [
                ['symbol' => 'BTCUSDT', 'base_currency' => 'BTC', 'quote_currency' => 'USDT', 'is_active' => true],
                ['symbol' => 'BTCUSDC', 'base_currency' => 'BTC', 'quote_currency' => 'USDC', 'is_active' => true],
                ['symbol' => 'ETHBTC', 'base_currency' => 'ETH', 'quote_currency' => 'BTC', 'is_active' => true],
            ];
            
            foreach ($pairs as $pair) {
                try {
                    CryptoPair::firstOrCreate(['symbol' => $pair['symbol']], $pair);
                } catch (\Exception $e) {
                    Log::error('Failed to create crypto pair', [
                        'symbol' => $pair['symbol'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        if (CryptoExchange::count() === 0) {
            $exchanges = [
                ['name' => 'binance', 'is_active' => true],
                ['name' => 'mexc', 'is_active' => true],
                ['name' => 'huobi', 'is_active' => true],
            ];
            
            foreach ($exchanges as $exchange) {
                try {
                    CryptoExchange::firstOrCreate(['name' => $exchange['name']], $exchange);
                } catch (\Exception $e) {
                    Log::error('Failed to create crypto exchange', [
                        'exchange' => $exchange['name'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('Crypto price job ultimately failed', [
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
    }
}