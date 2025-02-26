<?php

namespace Tests\Unit\Services;

use App\Services\CryptoPriceAggregator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class CryptoPriceAggregatorTest extends TestCase
{
    /**
     * Test that average price is calculated correctly.
     */
    public function test_calculate_average_price_with_valid_prices(): void
    {
        $aggregator = new CryptoPriceAggregator();
        
        $prices = collect([
            'binance' => 100.0,
            'mexc' => 101.0,
            'huobi' => 99.0
        ]);
        
        $average = $aggregator->calculateAveragePrice($prices);
        
        $this->assertEquals(100.0, $average);
    }
    
    /**
     * Test that average price returns null with empty collection.
     */
    public function test_calculate_average_price_with_empty_collection(): void
    {
        $aggregator = new CryptoPriceAggregator();
        
        $prices = collect([]);
        
        $average = $aggregator->calculateAveragePrice($prices);
        
        $this->assertNull($average);
    }
    
    /**
     * Test that average price ignores invalid values.
     */
    public function test_calculate_average_price_ignores_invalid_values(): void
    {
        $aggregator = new CryptoPriceAggregator();
        
        $prices = collect([
            'binance' => 100.0,
            'mexc' => -1.0, 
            'huobi' => 0.0, 
            'bybit' => 'invalid',
            'gate' => null 
        ]);
        
        $average = $aggregator->calculateAveragePrice($prices);
        
        $this->assertEquals(100.0, $average);
    }
    
    /**
     * Test that average price returns null when all values are invalid.
     */
    public function test_calculate_average_price_returns_null_when_all_values_invalid(): void
    {
        $aggregator = new CryptoPriceAggregator();
        
        $prices = collect([
            'binance' => -1.0,
            'mexc' => 0.0,
            'huobi' => null
        ]);
        
        $average = $aggregator->calculateAveragePrice($prices);
        
        $this->assertNull($average);
    }
}