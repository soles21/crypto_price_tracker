<?php

namespace App\Services\Crypto\Exchanges;

use App\Exceptions\ExchangeException;
use Illuminate\Support\Facades\Log;

class MexcExchange extends BaseExchange
{
    protected array $supportedPairs = ['BTCUSDT', 'BTCUSDC', 'ETHBTC'];

    public function __construct()
    {
        parent::__construct(
            'mexc',
            'https://api.mexc.com/api/v3',
            20
        );
    }

    public function getPrice(string $symbol): array
    {
        $symbol = $this->normalizeSymbol($symbol);
        
        try {
            $response = $this->makeRequest('/ticker/24hr', [
                'symbol' => $symbol
            ]);

            return [
                'symbol' => $symbol,
                'price' => (float) $response['lastPrice'],
                'high' => (float) $response['highPrice'],
                'low' => (float) $response['lowPrice'],
                'volume' => (float) $response['volume'],
                'priceChange' => (float) $response['priceChange'],
                'priceChangePercent' => (float) $response['priceChangePercent'],
                'timestamp' => $response['closeTime'],
                'exchange' => 'mexc'
            ];
        } catch (\Exception $e) {
            $this->handleError($e, $symbol);
        }
    }

    public function getPrices(array $symbols): array
    {
        try {
            $response = $this->makeRequest('/ticker/24hr');
            $prices = [];

            foreach ($symbols as $symbol) {
                $symbol = $this->normalizeSymbol($symbol);
                $tickerData = collect($response)->firstWhere('symbol', $symbol);
                
                if ($tickerData) {
                    $prices[$symbol] = [
                        'symbol' => $symbol,
                        'price' => (float) $tickerData['lastPrice'],
                        'high' => (float) $tickerData['highPrice'],
                        'low' => (float) $tickerData['lowPrice'],
                        'volume' => (float) $tickerData['volume'],
                        'priceChange' => (float) $tickerData['priceChange'],
                        'priceChangePercent' => (float) $tickerData['priceChangePercent'],
                        'timestamp' => $tickerData['closeTime'],
                        'exchange' => 'mexc'
                    ];
                }
            }
            
            return $prices;
        } catch (\Exception $e) {
            $this->handleError($e);
            return [];
        }
    }

    protected function checkHealth(): bool
    {
        try {
            $response = $this->makeRequest('/ping');
            return true;
        } catch (\Exception $e) {
            Log::error('MEXC health check failed', [
                'error'    => $e->getMessage(),
                'exchange' => $this->name
            ]);
            return false;
        }
    }

    protected function processResponse(array $response): array
    {
        // MEXC typically returns errors with a different structure
        if (isset($response['code']) && $response['code'] !== '200') {
            throw new ExchangeException(
                $response['msg'] ?? 'Unknown error',
                $this->name
            );
        }
        return $response;
    }
}