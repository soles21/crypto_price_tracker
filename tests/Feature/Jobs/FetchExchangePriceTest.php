<?php

namespace Tests\Unit\Jobs;

use App\Jobs\FetchExchangePrice;
use App\Services\FreeCryptoApiService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Mockery;

class FetchExchangePriceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test that the job fetches and caches price data correctly.
     */
    public function test_handle_fetches_and_caches_price_data(): void
    {
        $apiService = Mockery::mock(FreeCryptoApiService::class);
        
        $apiService->shouldReceive('getPairFromExchange')
            ->once()
            ->with('BTCUSDT', 'binance')
            ->andReturn([
                'symbol' => 'BTCUSDT',
                'last' => '50000.00',
                'last_btc' => '1',
                'lowest' => '49000.00',
                'highest' => '51000.00',
                'date' => '2023-01-01 12:00:00',
                'daily_change_percentage' => '2.5'
            ]);
        
        $cachePutCalled = false;
        Cache::shouldReceive('put')
            ->once()
            ->with(
                'crypto_price_data_BTCUSDT_binance',
                Mockery::on(function ($value) {
                    return isset($value['price']) && $value['price'] === 50000.0 &&
                           isset($value['data']);
                }),
                Mockery::any()
            )
            ->andReturnUsing(function() use (&$cachePutCalled) {
                $cachePutCalled = true;
                return true;
            });
        
        $job = new FetchExchangePrice('BTCUSDT', 'binance');
        
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('batch')->andReturn(null);
        
        $job->handle($apiService);
        
        $this->assertTrue($cachePutCalled, 'Cache::put was not called');
    }
    
    /**
     * Test that the job handles invalid price data appropriately.
     */
    public function test_handle_handles_invalid_price_data(): void
    {
        $apiService = Mockery::mock(FreeCryptoApiService::class);
        
        $apiService->shouldReceive('getPairFromExchange')
            ->once()
            ->with('BTCUSDT', 'binance')
            ->andReturn([
                'symbol' => 'BTCUSDT',
                'date' => '2023-01-01 12:00:00',
            ]);
        
        Cache::shouldReceive('put')->never();
        
        $job = new FetchExchangePrice('BTCUSDT', 'binance');
        
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('batch')->andReturn(null);
        
        try {
            $job->handle($apiService);
            $this->assertTrue(true, 'Job handled invalid data without errors');
        } catch (\Exception $e) {
            $this->fail('Job failed to handle invalid data: ' . $e->getMessage());
        }
    }
    
    /**
     * Test that the job handles API errors appropriately.
     */
    public function test_handle_handles_api_errors(): void
    {
        $apiService = Mockery::mock(FreeCryptoApiService::class);
        
        $apiService->shouldReceive('getPairFromExchange')
            ->once()
            ->with('BTCUSDT', 'binance')
            ->andReturn(null);
        
        Cache::shouldReceive('put')->never();
        
        $job = new FetchExchangePrice('BTCUSDT', 'binance');
        
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('batch')->andReturn(null);
        
        try {
            $job->handle($apiService);
            $this->assertTrue(true, 'Job handled API error without exceptions');
        } catch (\Exception $e) {
            $this->fail('Job failed to handle API error: ' . $e->getMessage());
        }
    }
    
    /**
     * Test that the job has correct getter methods.
     */
    public function test_getter_methods(): void
    {
        $job = new FetchExchangePrice('BTCUSDT', 'binance');
        
        $this->assertEquals('BTCUSDT', $job->getPair());
        $this->assertEquals('binance', $job->getExchange());
        $this->assertEquals('crypto_price_data_BTCUSDT_binance', $job->getCacheKey());
    }
}