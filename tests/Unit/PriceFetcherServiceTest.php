<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Crypto\PriceFetcher\PriceFetcherService;
use App\Models\CryptoPair;
use App\Models\CryptoExchange;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PriceFetcherServiceTest extends TestCase
{
    use RefreshDatabase;

    private PriceFetcherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear database first
        CryptoPair::query()->delete();
        CryptoExchange::query()->delete();
        
        // Seed test data
        CryptoPair::create([
            'symbol' => 'BTCUSDT',
            'base_currency' => 'BTC',
            'quote_currency' => 'USDT',
            'is_active' => true
        ]);

        CryptoExchange::create([
            'name' => 'binance',
            'is_active' => true
        ]);

        $this->service = app(PriceFetcherService::class);
    }

    /** @test */
    public function it_fetches_prices_for_all_pairs()
    {
        $prices = $this->service->fetchAllPrices();

        $this->assertNotEmpty($prices);
        $this->assertArrayHasKey('BTCUSDT', $prices);
        
        foreach ($prices as $symbol => $priceData) {
            $this->assertArrayHasKey('binance', $priceData);
            $this->assertIsArray($priceData['binance']);
            $this->assertArrayHasKey('price', $priceData['binance']);
        }
    }

    /** @test */
    public function it_calculates_aggregate_statistics_correctly()
    {
        $prices = [
            'binance' => [
                'price' => 50000.0,
                'high' => 51000.0,
                'low' => 49000.0,
                'volume' => 100.0,
                'priceChange' => 1000.0,
                'priceChangePercent' => 2.0
            ],
            'mexc' => [
                'price' => 50100.0,
                'high' => 51100.0,
                'low' => 49100.0,
                'volume' => 90.0,
                'priceChange' => 900.0,
                'priceChangePercent' => 1.8
            ]
        ];

        $stats = $this->service->calculateAggregateStatistics($prices);

        $this->assertEquals(50050.0, $stats['average_price']);
        $this->assertEquals(51100.0, $stats['high_price']);
        $this->assertEquals(49000.0, $stats['low_price']);
        $this->assertEquals(190.0, $stats['volume']);
        $this->assertEquals(950.0, $stats['price_change']);
        $this->assertEquals(1.9, $stats['price_change_percent']);
        $this->assertEquals(2, $stats['number_of_sources']);
    }

    public function tearDown(): void
    {
        CryptoPair::query()->delete();
        CryptoExchange::query()->delete();
        parent::tearDown();
    }
}