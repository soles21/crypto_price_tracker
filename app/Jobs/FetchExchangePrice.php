<?php

namespace App\Jobs;

use App\Services\FreeCryptoApiService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchExchangePrice implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The cryptocurrency pair to fetch
     *
     * @var string
     */
    protected string $pair;

    /**
     * The exchange to fetch from
     *
     * @var string
     */
    protected string $exchange;

    /**
     * Number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Timeout in seconds
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @param string $pair
     * @param string $exchange
     */
    public function __construct(string $pair, string $exchange)
    {
        $this->pair = $pair;
        $this->exchange = $exchange;
    }

    /**
     * Execute the job.
     */
    public function handle(FreeCryptoApiService $apiService): void
    {
        // Skip if the batch has been cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        try {
            $data = $apiService->getPairFromExchange($this->pair, $this->exchange);
            
            if ($data && isset($data['last'])) {
                $price = (float) $data['last'];
                
                if ($price > 0) {
                    // Store the price in cache for later aggregation
                    $cacheKey = $this->getCacheKey();
                    Cache::put($cacheKey, [
                        'price' => $price,
                        'data' => $data
                    ], now()->addMinutes(5));
                    
                    Log::info("Fetched price for {$this->pair} from {$this->exchange}: {$price}");
                    return;
                }
            }
            
            Log::warning("Invalid price data for {$this->pair} from {$this->exchange}", [
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch price for {$this->pair} from {$this->exchange}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Rethrow to retry
            throw $e;
        }
    }

    /**
     * Get cache key for this pair and exchange
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        return "crypto_price_data_{$this->pair}_{$this->exchange}";
    }

    /**
     * Get the cryptocurrency pair
     *
     * @return string
     */
    public function getPair(): string
    {
        return $this->pair;
    }

    /**
     * Get the exchange
     *
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }
}