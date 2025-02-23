<?php
namespace App\Services\Crypto\Exchanges;
use App\Exceptions\ExchangeException;
use Illuminate\Support\Facades\Log;

class HuobiExchange extends BaseExchange
{
    protected array $supportedPairs = ['BTCUSDT', 'BTCUSDC', 'ETHBTC'];

    public function __construct()
    {
        parent::__construct(
            'huobi',
            'https://api.huobi.pro',
            20
        );
    }

    public function getPrice(string $symbol): array
    {
        $symbol = strtolower($this->normalizeSymbol($symbol));
        
        try {
            $response = $this->makeRequest('/market/detail/merged', [
                'symbol' => $symbol
            ]);
            
            $tick = $response['tick'];
            $close = $tick['close'];
            $open = $tick['open'];
            $priceChange = $close - $open;
            $priceChangePercent = ($open > 0) ? ($priceChange / $open) * 100 : 0;
            
            return [
                'symbol' => strtoupper($symbol),
                'price' => (float) $close,
                'high' => (float) $tick['high'],
                'low' => (float) $tick['low'],
                'volume' => (float) $tick['vol'],
                'priceChange' => $priceChange,
                'priceChangePercent' => $priceChangePercent,
                'timestamp' => $response['ts'],
                'exchange' => 'huobi'
            ];
        } catch (\Exception $e) {
            $this->handleError($e, $symbol);
        }
    }

    public function getPrices(array $symbols): array
    {
        try {
            $prices = [];
            foreach ($symbols as $symbol) {
                $normalizedSymbol = strtolower($this->normalizeSymbol($symbol));
                
                try {
                    $response = $this->makeRequest('/market/detail/merged', [
                        'symbol' => $normalizedSymbol
                    ]);
                    
                    $tick = $response['tick'];
                    $close = $tick['close'];
                    $open = $tick['open'];
                    $priceChange = $close - $open;
                    $priceChangePercent = ($open > 0) ? ($priceChange / $open) * 100 : 0;
                    
                    $prices[$symbol] = [
                        'symbol' => strtoupper($normalizedSymbol),
                        'price' => (float) $close,
                        'high' => (float) $tick['high'],
                        'low' => (float) $tick['low'],
                        'volume' => (float) $tick['vol'],
                        'priceChange' => $priceChange,
                        'priceChangePercent' => $priceChangePercent,
                        'timestamp' => $response['ts'],
                        'exchange' => 'huobi'
                    ];
                } catch (\Exception $e) {
                    Log::warning("Failed to fetch price for {$symbol} on Huobi", [
                        'error' => $e->getMessage()
                    ]);
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
        $response = $this->makeRequest('/v1/common/timestamp');
        return isset($response['status']) 
            && $response['status'] === 'ok' 
            && isset($response['data']);
    } catch (\Exception $e) {
        Log::error('Huobi health check failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
	}

    protected function processResponse(array $response): array
    {
        if (isset($response['status']) && $response['status'] !== 'ok') {
            throw new ExchangeException(
                $response['err-msg'] ?? 'Unknown error',
                $this->name
            );
        }
        return $response;
    }
}