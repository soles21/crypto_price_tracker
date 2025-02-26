<?php

namespace App\Services;

use App\Jobs\FetchExchangePrice;
use App\Jobs\ProcessAggregatedPrice;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class CryptoPriceAggregator
{
    /**
     * Dispatch jobs to fetch prices from all exchanges in parallel
     *
     * @return void
     */
    public function dispatchPriceFetchJobs(): void
    {
        $pairs = config('crypto.pairs');
        $exchanges = config('crypto.exchanges');

        if (empty($pairs) || empty($exchanges)) {
            Log::warning('No cryptocurrency pairs or exchanges configured.');
            return;
        }

        foreach ($pairs as $pair) {
            $jobs = [];

            foreach ($exchanges as $exchange) {
                $jobs[] = new FetchExchangePrice($pair, $exchange);
            }

            try {
                Bus::batch($jobs)
                    ->then(function (Batch $batch) use ($pair, $exchanges) {
                        dispatch(new ProcessAggregatedPrice($pair, $exchanges));
                    })
                    ->catch(function (Batch $batch, Throwable $e) use ($pair) {
                        Log::error("Error in price fetch batch for {$pair}", [
                            'error' => $e->getMessage(),
                        ]);
                    })
                    ->dispatch();
            } catch (Throwable $e) {
                Log::error("Failed to dispatch price fetch jobs for {$pair}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Calculate average price from multiple exchange prices
     *
     * @param Collection $prices
     * @return float|null
     */
    public function calculateAveragePrice(Collection $prices): ?float
    {
        if ($prices->isEmpty()) {
            return null;
        }

        $validPrices = $prices->filter(function ($price) {
            return is_numeric($price) && $price > 0;
        });

        if ($validPrices->isEmpty()) {
            return null;
        }

        return $validPrices->avg();
    }
}