<?php

namespace Tests\Unit\Services;

use App\Services\FreeCryptoApiService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class FreeCryptoApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Log::shouldReceive('error')->andReturnNull();
        Log::shouldReceive('warning')->andReturnNull();
        
        Config::shouldReceive('get')->with('crypto.api_url', null)->andReturn('https://api.freecryptoapi.com/v1');
        Config::shouldReceive('get')->with('crypto.api_key', null)->andReturn('test-api-key');
        Config::shouldReceive('get')->with('crypto.cache_ttl', null)->andReturn(60);
        
        Config::shouldReceive('offsetGet')->withAnyArgs()->andReturnNull();
        Config::shouldReceive('offsetExists')->withAnyArgs()->andReturn(false);
        
        Config::shouldReceive('get')->withAnyArgs()->andReturnNull();
    }
    /**
     * Test that the service returns price data when API call is successful.
     */
    public function test_get_pair_from_exchange_returns_data_on_successful_api_call(): void
    {
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('successful')->andReturn(true);
        $mockResponse->shouldReceive('json')->andReturn([
            'status' => 'success',
            'symbols' => [
                [
                    'symbol' => 'BTCUSDT',
                    'last' => '50000.00',
                    'last_btc' => '1',
                    'lowest' => '49000.00',
                    'highest' => '51000.00',
                    'date' => '2023-01-01 12:00:00',
                    'daily_change_percentage' => '2.5'
                ]
            ]
        ]);
        
        Http::shouldReceive('withHeaders')
            ->once()
            ->with([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer test-api-key'
            ])
            ->andReturnSelf();
            
        Http::shouldReceive('get')
            ->once()
            ->with('https://api.freecryptoapi.com/v1/getData', [
                'symbol' => 'BTCUSDT@binance'
            ])
            ->andReturn($mockResponse);
            
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function($key, $ttl, $callback) {
                return $callback();
            });
            
        $service = new FreeCryptoApiService();
        $result = $service->getPairFromExchange('BTCUSDT', 'binance');
        
        $this->assertIsArray($result);
        $this->assertEquals('BTCUSDT', $result['symbol']);
        $this->assertEquals('50000.00', $result['last']);
    }
    
    /**
     * Test that the service returns null when API returns an error.
     */
    public function test_get_pair_from_exchange_returns_null_on_api_error(): void
    {
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('successful')->andReturn(false);
        $mockResponse->shouldReceive('status')->andReturn(401);
        $mockResponse->shouldReceive('body')->andReturn(json_encode([
            'status' => 'error',
            'message' => 'Invalid API key'
        ]));
        
        Http::shouldReceive('withHeaders')
            ->once()
            ->andReturnSelf();
            
        Http::shouldReceive('get')
            ->once()
            ->andReturn($mockResponse);
            
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function($key, $ttl, $callback) {
                return $callback();
            });
            
        $service = new FreeCryptoApiService();
        $result = $service->getPairFromExchange('BTCUSDT', 'binance');
        
        $this->assertNull($result);
    }
    
    /**
     * Test that the service returns null when API returns no data.
     */
    public function test_get_pair_from_exchange_returns_null_on_empty_data(): void
    {
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('successful')->andReturn(true);
        $mockResponse->shouldReceive('json')->andReturn([
            'status' => 'success',
            'symbols' => []
        ]);
        
        Http::shouldReceive('withHeaders')
            ->once()
            ->andReturnSelf();
            
        Http::shouldReceive('get')
            ->once()
            ->andReturn($mockResponse);
            
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function($key, $ttl, $callback) {
                return $callback();
            });
            
        $service = new FreeCryptoApiService();
        $result = $service->getPairFromExchange('BTCUSDT', 'binance');
        
        $this->assertNull($result);
    }
}