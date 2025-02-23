<?php

namespace App\Services\Crypto\Exchanges;

interface ExchangeClientInterface
{
    /**
     * Get the current price for a specific trading pair
     *
     * @param string $symbol Trading pair symbol (e.g., 'BTCUSDT')
     * @return array
     * @throws \App\Exceptions\ExchangeException
     */
    public function getPrice(string $symbol): array;

    /**
     * Get prices for multiple trading pairs
     *
     * @param array $symbols Array of trading pair symbols
     * @return array
     * @throws \App\Exceptions\ExchangeException
     */
    public function getPrices(array $symbols): array;

    /**
     * Get exchange status
     *
     * @return bool
     */
    public function isHealthy(): bool;

    /**
     * Get exchange name
     *
     * @return string
     */
    public function getExchangeName(): string;

    /**
     * Get supported trading pairs
     *
     * @return array
     */
    public function getSupportedPairs(): array;

    /**
     * Validate if a trading pair is supported
     *
     * @param string $symbol
     * @return bool
     */
    public function isSymbolSupported(string $symbol): bool;
}