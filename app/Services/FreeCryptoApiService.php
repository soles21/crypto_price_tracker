<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FreeCryptoApiService
{
    /**
     * Base API URL
     * 
     * @var string
     */
    protected string $baseUrl;
    
    /**
     * API key
     * 
     * @var string|null
     */
    protected ?string $apiKey;
    
    /**
     * Cache TTL in seconds
     * 
     * @var int
     */
    protected int $cacheTtl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->baseUrl = config('crypto.api_url') ?? 'https://api.freecryptoapi.com/v1';
        $this->apiKey = config('crypto.api_key');
        $this->cacheTtl = (int) (config('crypto.cache_ttl') ?? 60);
    }

    /**
     * Get cryptocurrency data from specific exchange
     * 
     * @param string $pair
     * @param string $exchange
     * @return array|null
     */
    public function getPairFromExchange(string $pair, string $exchange): ?array
    {
        $cacheKey = "crypto_price_{$pair}_{$exchange}";
        
        try {
            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($pair, $exchange) {
                $symbol = "{$pair}@{$exchange}";
                
                $response = Http::withHeaders($this->getHeaders())
                    ->get("{$this->baseUrl}/getData", [
                        'symbol' => $symbol
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['status']) && $data['status'] === 'success' && !empty($data['symbols'][0])) {
                        return $data['symbols'][0];
                    }
                    
                    Log::warning("FreeCryptoAPI returned no data for {$symbol}", [
                        'response' => $data
                    ]);
                    
                    return null;
                }
                
                Log::error("Failed to fetch price data from FreeCryptoAPI for {$symbol}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return null;
            });
        } catch (RequestException $e) {
            Log::error("HTTP request error when fetching price for {$pair} from {$exchange}", [
                'error' => $e->getMessage()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error("Unexpected error when fetching price for {$pair} from {$exchange}", [
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Get HTTP request headers
     * 
     * @return array
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];
        
        if ($this->apiKey) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }
        
        return $headers;
    }
}