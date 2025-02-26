<?php

namespace App\Jobs;

use App\Events\CryptoPriceUpdated;
use App\Models\CryptoPrice;
use App\Services\CryptoPriceAggregator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessAggregatedPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The cryptocurrency pair to process
     *
     * @var string
     */
    protected string $pair;

    /**
     * The exchanges to aggregate prices from
     *
     * @var array
     */
    protected array $exchanges;

    /**
     * Create a new job instance.
     *
     * @param string $pair
     * @param array $exchanges
     */
    public function __construct(string $pair, array $exchanges)
    {
        $this->pair = $pair;
        $this->exchanges = $exchanges;
    }

    /**
     * Execute the job.
     */
    public function handle(CryptoPriceAggregator $aggregator): void
    {
        try {
            $exchangePrices = $this->collectPricesFromExchanges();
            
            if ($exchangePrices->isEmpty()) {
                Log::warning("No valid prices found for {$this->pair} from any exchange");
                return;
            }
            
            $averagePrice = $aggregator->calculateAveragePrice($exchangePrices->pluck('price'));
            
            if ($averagePrice === null) {
                Log::warning("Could not calculate average price for {$this->pair}");
                return;
            }
            
            $this->saveAndBroadcast($averagePrice, $exchangePrices);
            
        } catch (\Exception $e) {
            Log::error("Error processing aggregated price for {$this->pair}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Collect prices from all exchanges
     *
     * @return Collection
     */
    protected function collectPricesFromExchanges(): Collection
    {
        $prices = collect();
        
        foreach ($this->exchanges as $exchange) {
            $cacheKey = "crypto_price_data_{$this->pair}_{$exchange}";
            $data = Cache::get($cacheKey);
            
            if ($data && isset($data['price']) && $data['price'] > 0) {
                $prices[$exchange] = $data;
            }
        }
        
        return $prices;
    }

    /**
     * Save the price to the database and broadcast via WebSocket
     *
     * @param float $averagePrice
     * @param Collection $exchangePrices
     * @return void
     */
    protected function saveAndBroadcast(float $averagePrice, Collection $exchangePrices): void
    {
        $priceRecord = CryptoPrice::firstOrNew(['pair' => $this->pair]);
        
        $priceRecord->updatePriceChange($averagePrice);
        
        $priceRecord->exchange_prices = $exchangePrices->map(function ($data) {
            return [
                'price' => $data['price'],
                'data' => $data['data'] ?? null,
            ];
        })->toArray();
        
        $priceRecord->exchanges = $exchangePrices->keys()->toArray();
        
        $priceRecord->save();
        
        event(new CryptoPriceUpdated($priceRecord));
    }
}