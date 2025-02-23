<?php

namespace App\Services\Crypto\PriceFetcher;

interface PriceFetcherInterface
{
    /**
     * Fetch prices for all configured pairs from all exchanges
     *
     * @return array
     */
    public function fetchAllPrices(): array;

    /**
     * Fetch price for a specific pair from all exchanges
     *
     * @param string $symbol
     * @return array
     */
    public function fetchPriceForPair(string $symbol): array;

    /**
     * Calculate aggregate price statistics
     *
     * @param array $prices
     * @return array
     */
    public function calculateAggregateStatistics(array $prices): array;
}