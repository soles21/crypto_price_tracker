<?php

namespace Tests\Feature\Jobs;

use App\Events\CryptoPriceUpdated;
use App\Jobs\ProcessAggregatedPrice;
use App\Models\CryptoPrice;
use App\Services\CryptoPriceAggregator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class ProcessAggregatedPriceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that the job saves price data and broadcasts an event.
     */
    public function test_job_saves_data_and_broadcasts_event(): void
    {
        Event::fake([CryptoPriceUpdated::class]);
        
        $pair = 'BTCUSDT';
        $exchanges = ['binance', 'mexc'];
        
        // Mock the cache data
        Cache::shouldReceive('get')
            ->with("crypto_price_data_{$pair}_binance")
            ->andReturn([
                'price' => 50000.00,
                'data' => ['last' => '50000.00', 'symbol' => 'BTCUSDT']
            ]);
            
        Cache::shouldReceive('get')
            ->with("crypto_price_data_{$pair}_mexc")
            ->andReturn([
                'price' => 50000.00,
                'data' => ['last' => '50000.00', 'symbol' => 'BTCUSDT']
            ]);
        
        // Mock the aggregator
        $aggregator = Mockery::mock(CryptoPriceAggregator::class);
        $aggregator->shouldReceive('calculateAveragePrice')
            ->once()
            ->with(Mockery::type(Collection::class))
            ->andReturn(50000.00);
            
        // Create and run the job
        $job = new ProcessAggregatedPrice($pair, $exchanges);
        $job->handle($aggregator);
        
        // Assert the data was saved
        $this->assertDatabaseHas('crypto_prices', [
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
        ]);
        
        // Assert the event was dispatched
        Event::assertDispatched(CryptoPriceUpdated::class, function ($event) {
            return $event->cryptoPrice->pair === 'BTCUSDT' 
                && $event->cryptoPrice->price == 50000.00;
        });
    }
    
    /**
     * Test that the job does not broadcast when no valid prices are found.
     */
    public function test_job_does_not_broadcast_when_no_valid_prices(): void
    {
        Event::fake([CryptoPriceUpdated::class]);
        
        $pair = 'BTCUSDT';
        $exchanges = ['binance', 'mexc'];
        
        // Mock the cache data (empty or invalid)
        Cache::shouldReceive('get')
            ->with("crypto_price_data_{$pair}_binance")
            ->andReturn(null);
            
        Cache::shouldReceive('get')
            ->with("crypto_price_data_{$pair}_mexc")
            ->andReturn(null);
        
        // Create and run the job
        $job = new ProcessAggregatedPrice($pair, $exchanges);
        $job->handle(new CryptoPriceAggregator());
        
        // Assert no event was dispatched
        Event::assertNotDispatched(CryptoPriceUpdated::class);
    }
    
    /**
     * Test that the job does not broadcast when the aggregator returns null.
     */
    public function test_job_does_not_broadcast_when_aggregator_returns_null(): void
    {
        Event::fake([CryptoPriceUpdated::class]);
        
        $pair = 'BTCUSDT';
        $exchanges = ['binance', 'mexc'];
        
        // Mock the cache data
        Cache::shouldReceive('get')
            ->with("crypto_price_data_{$pair}_binance")
            ->andReturn([
                'price' => -1.00,  // Invalid price
                'data' => ['last' => '-1.00', 'symbol' => 'BTCUSDT']
            ]);
            
        Cache::shouldReceive('get')
            ->with("crypto_price_data_{$pair}_mexc")
            ->andReturn([
                'price' => 0.00,  // Invalid price
                'data' => ['last' => '0.00', 'symbol' => 'BTCUSDT']
            ]);
        
        // Create and run the job
        $job = new ProcessAggregatedPrice($pair, $exchanges);
        $job->handle(new CryptoPriceAggregator());
        
        // Assert no event was dispatched
        Event::assertNotDispatched(CryptoPriceUpdated::class);
    }
} 