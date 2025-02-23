<?php

namespace App\Services\Crypto\Exchanges;

use App\Services\Crypto\Exchanges\ExchangeClientInterface;
use App\Exceptions\ExchangeException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

abstract class BaseExchange implements ExchangeClientInterface
{
    protected string $name;
    protected string $baseUrl;
    protected array $supportedPairs = [];
    protected int $rateLimit;
    protected int $timeout = 10;
    protected int $retryAttempts = 3;
    protected int $retryDelay = 1000;

    protected function __construct(string $name, string $baseUrl, int $rateLimit = 20)
    {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->rateLimit = $rateLimit;
    }

    public function getExchangeName(): string
    {
        return $this->name;
    }

    public function isSymbolSupported(string $symbol): bool
    {
        return in_array(strtoupper($symbol), $this->supportedPairs);
    }

    public function getSupportedPairs(): array
    {
        return $this->supportedPairs;
    }

    public function isHealthy(): bool
    {
        try {
            return $this->checkHealth();
        } catch (\Exception $e) {
            Log::error("Health check failed for {$this->name}", [
                'error' => $e->getMessage(),
                'exchange' => $this->name
            ]);
            return false;
        }
    }

    protected function makeRequest(string $endpoint, array $params = [], string $method = 'GET'): array
    {
        $cacheKey = $this->getCacheKey($endpoint, $params);
        
        return Cache::remember($cacheKey, 5, function () use ($endpoint, $params, $method) {
            return retry($this->retryAttempts, function () use ($endpoint, $params, $method) {
                try {
                    $response = Http::timeout($this->timeout)
                        ->withHeaders($this->getHeaders())
                        ->{strtolower($method)}($this->baseUrl . $endpoint, $params);

                    if ($response->failed()) {
                        throw new ExchangeException(
                            "Request failed: {$response->status()}",
                            $this->name,
                            $params['symbol'] ?? null,
                            $response->body()
                        );
                    }

                    return $this->processResponse($response->json());
                } catch (\Exception $e) {
                    Log::error("Request failed for {$this->name}", [
                        'endpoint' => $endpoint,
                        'params' => $params,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }, $this->retryDelay);
        });
    }

    protected function getCacheKey(string $endpoint, array $params): string
    {
        return "exchange:{$this->name}:" . md5($endpoint . serialize($params));
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => 'CryptoAggregator/1.0'
        ];
    }

    protected function normalizeSymbol(string $symbol): string
    {
        return strtoupper(str_replace(['/', '-', '_'], '', $symbol));
    }

    protected function handleError(\Exception $e, string $symbol = null): void
    {
        Log::error("Exchange error: {$this->name}", [
            'error' => $e->getMessage(),
            'symbol' => $symbol,
            'exchange' => $this->name
        ]);

        throw new ExchangeException(
            "Exchange error: {$e->getMessage()}",
            $this->name,
            $symbol
        );
    }

    abstract protected function checkHealth(): bool;
    abstract protected function processResponse(array $response): array;
    abstract public function getPrice(string $symbol): array;
    abstract public function getPrices(array $symbols): array;
}