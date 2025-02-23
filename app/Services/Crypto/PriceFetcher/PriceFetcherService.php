<?php

namespace App\Services\Crypto\PriceFetcher;

use App\Models\CryptoPair;
use App\Models\CryptoPrice;
use App\Models\PriceAggregate;
use App\Services\Crypto\Exchanges\ExchangeFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PriceFetcherService implements PriceFetcherInterface
{
    protected $exchanges = [];
    protected Collection $pairs;
    protected float $globalTimeout = 10; // 10 seconds timeout

    public function __construct()
    {
        $this->initializeExchanges();
        $this->pairs = CryptoPair::where('is_active', true)->get();
    }

    protected function initializeExchanges(): void
    {
        try {
            $configuredExchanges = explode(',', config('crypto.exchanges', 'binance,mexc,huobi'));
            $initializedExchanges = [];

            foreach ($configuredExchanges as $exchange) {
                $exchange = trim($exchange);
                try {
                    $exchangeClient = ExchangeFactory::create($exchange);
                    
                    // Add additional health check
                    if ($exchangeClient->isHealthy()) {
                        $initializedExchanges[$exchange] = $exchangeClient;
                    } else {
                        Log::warning("Exchange failed health check", [
                            'exchange' => $exchange
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to initialize exchange", [
                        'exchange' => $exchange,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Ensure at least one exchange is available
            if (empty($initializedExchanges)) {
                Log::critical("No exchanges could be initialized");
                throw new \RuntimeException("No cryptocurrency exchanges are available");
            }

            $this->exchanges = $initializedExchanges;
        } catch (\Exception $e) {
            Log::error("Critical failure in exchange initialization", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function fetchAllPrices(): array
    {
        $startTime = microtime(true);

        try {
            $allPrices = [];
            $timestamp = Carbon::now();

            foreach ($this->pairs as $pair) {
                // Check if global timeout is exceeded
                if ((microtime(true) - $startTime) > $this->globalTimeout) {
                    Log::warning('Global timeout exceeded during price fetching', [
                        'completed_pairs' => count($allPrices)
                    ]);
                    break;
                }

                $prices = $this->fetchPriceForPair($pair->symbol);
                if (!empty($prices)) {
                    $allPrices[$pair->symbol] = $prices;
                    $this->storePrices($pair, $prices, $timestamp);
                    $aggregate = $this->calculateAggregateStatistics($prices);
                    $this->storeAggregate($pair, $aggregate, $timestamp);
                }
            }
            return $allPrices;
        } catch (\Exception $e) {
            Log::error('Error in fetchAllPrices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function fetchPriceForPair(string $symbol): array
    {
        try {
            $prices = [];
            $failedExchanges = [];

            foreach ($this->exchanges as $exchangeName => $client) {
                try {
                    // Add health check before attempting to fetch
                    if (!$client->isHealthy()) {
                        $failedExchanges[] = $exchangeName;
                        continue;
                    }

                    if ($client->isSymbolSupported($symbol)) {
                        $price = $client->getPrice($symbol);
                        $prices[$exchangeName] = $price;
                    }
                } catch (\Exception $e) {
                    $failedExchanges[] = $exchangeName;
                    Log::warning("Failed to fetch price", [
                        'exchange' => $exchangeName,
                        'symbol' => $symbol,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Log if all exchanges failed
            if (empty($prices) && !empty($failedExchanges)) {
                Log::error("No prices fetched for symbol", [
                    'symbol' => $symbol,
                    'failed_exchanges' => $failedExchanges
                ]);
            }

            return $prices;
        } catch (\Exception $e) {
            Log::error('Critical error in fetchPriceForPair', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'symbol' => $symbol
            ]);
            throw $e;
        }
    }

    public function calculateAggregateStatistics(array $prices): array
    {
        $validPrices = collect($prices)->filter(function ($price) {
            // Add more robust validation
            return isset($price['price']) 
                   && $price['price'] > 0 
                   && is_numeric($price['price'])
                   && isset($price['volume']) 
                   && $price['volume'] >= 0;
        });

        if ($validPrices->isEmpty()) {
            Log::warning('No valid prices found for aggregation', [
                'input_prices' => $prices
            ]);
            
            return [
                'average_price' => 0,
                'high_price' => 0,
                'low_price' => 0,
                'price_change' => 0,
                'price_change_percent' => 0,
                'volume' => 0,
                'number_of_sources' => 0,
                'exchange_ids' => [],
                'exchange_data' => []
            ];
        }

        $priceValues = $validPrices->pluck('price');
        $volumeValues = $validPrices->pluck('volume');
        $changes = $validPrices->pluck('priceChange');
        $changePercents = $validPrices->pluck('priceChangePercent');

        $totalVolume = $volumeValues->sum();
        $weightedPrice = $totalVolume > 0 
            ? $validPrices->map(function ($price) use ($totalVolume) {
                return $price['price'] * ($price['volume'] / $totalVolume);
            })->sum()
            : $priceValues->average();

        $exchangeData = $validPrices->map(function ($price, $exchange) {
            return [
                'name' => $exchange,
                'price' => $price['price'],
                'high' => $price['high'] ?? $price['price'],
                'low' => $price['low'] ?? $price['price'],
                'volume' => $price['volume'] ?? 0,
                'change' => $price['priceChange'] ?? 0,
                'change_percent' => $price['priceChangePercent'] ?? 0
            ];
        })->values()->toArray();

        return [
            'average_price' => $weightedPrice,
            'high_price' => $priceValues->max(),
            'low_price' => $priceValues->min(),
            'price_change' => $changes->average(),
            'price_change_percent' => $changePercents->average(),
            'volume' => $volumeValues->sum(),
            'number_of_sources' => $validPrices->count(),
            'exchange_ids' => $validPrices->keys()->toArray(),
            'exchange_data' => $exchangeData
        ];
    }

    protected function storePrices(CryptoPair $pair, array $prices, Carbon $timestamp): void
{
    DB::beginTransaction();
    try {
        foreach ($prices as $exchangeName => $priceData) {
            $exchangeId = $this->getExchangeId($exchangeName);

            // Find or create the initial record
            $cryptoPrice = CryptoPrice::firstOrNew([
                'pair_id' => $pair->id,
                'exchange_id' => $exchangeId
            ]);

            // Update the existing or new record
            $cryptoPrice->fill([
                'price' => $priceData['price'],
                'high' => $priceData['high'] ?? $priceData['price'],
                'low' => $priceData['low'] ?? $priceData['price'],
                'volume' => $priceData['volume'] ?? 0,
                'price_change' => $priceData['priceChange'] ?? 0,
                'price_change_percent' => $priceData['priceChangePercent'] ?? 0,
                'fetched_at' => $timestamp,
                'is_valid' => true,
                'raw_data' => $priceData
            ]);

            $cryptoPrice->save();
        }
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Failed to store prices", [
            'error' => $e->getMessage(),
            'pair' => $pair->symbol,
            'prices' => $prices
        ]);
    }
}

protected function storeAggregate(CryptoPair $pair, array $aggregate, Carbon $timestamp): void
{
    try {
        // Find or create the initial aggregate record
        $priceAggregate = PriceAggregate::firstOrNew([
            'pair_id' => $pair->id
        ]);

        // Update the existing or new record
        $priceAggregate->fill([
            'average_price' => $aggregate['average_price'],
            'high_price' => $aggregate['high_price'],
            'low_price' => $aggregate['low_price'],
            'price_change' => $aggregate['price_change'],
            'price_change_percent' => $aggregate['price_change_percent'],
            'volume' => $aggregate['volume'],
            'number_of_sources' => $aggregate['number_of_sources'],
            'exchange_ids' => $aggregate['exchange_ids'],
            'exchange_data' => $aggregate['exchange_data'],
            'calculated_at' => $timestamp
        ]);

        $priceAggregate->save();
    } catch (\Exception $e) {
        Log::error("Failed to store aggregate", [
            'error' => $e->getMessage(),
            'pair' => $pair->symbol,
            'aggregate' => $aggregate
        ]);
    }
}

    protected function getExchangeId(string $exchangeName): int
    {
        return DB::table('crypto_exchanges')
            ->where('name', $exchangeName)
            ->value('id');
    }
}