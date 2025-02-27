<?php

namespace Tests\Feature\Events;

use App\Events\CryptoPriceUpdated;
use App\Models\CryptoPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class CryptoPriceUpdatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the CryptoPriceUpdated event is properly broadcasted.
     */
    public function test_event_is_broadcasted_with_correct_data(): void
    {
        Event::fake([CryptoPriceUpdated::class]);
        
        $cryptoPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'previous_price' => 49000.00,
            'price_change' => 1000.00,
            'price_change_percentage' => 2.04,
            'exchanges' => ['binance', 'mexc'],
            'exchange_prices' => [
                'binance' => ['price' => 50000.00, 'data' => null],
                'mexc' => ['price' => 50000.00, 'data' => null],
            ],
        ]);
        
        event(new CryptoPriceUpdated($cryptoPrice));
        
        Event::assertDispatched(CryptoPriceUpdated::class, function ($event) use ($cryptoPrice) {
            return $event->cryptoPrice->id === $cryptoPrice->id 
                && $event->cryptoPrice->pair === 'BTCUSDT';
        });
    }
    
    /**
     * Test that the event returns the correct broadcast channel.
     */
    public function test_broadcast_on_returns_correct_channel(): void
    {
        $cryptoPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
        ]);
        
        $event = new CryptoPriceUpdated($cryptoPrice);
        $channels = $event->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertEquals('crypto-prices', $channels[0]->name);
    }
    
    /**
     * Test that the event returns the correct broadcast name.
     */
    public function test_broadcast_as_returns_correct_name(): void
    {
        $cryptoPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
        ]);
        
        $event = new CryptoPriceUpdated($cryptoPrice);
        $name = $event->broadcastAs();
        
        $this->assertEquals('crypto.price.updated', $name);
    }
    
    /**
     * Test that the event returns the correct data for broadcasting.
     */
    public function test_broadcast_with_returns_correct_data(): void
    {
        $cryptoPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'previous_price' => 49000.00,
            'price_change' => 1000.00,
            'price_change_percentage' => 2.04,
            'exchanges' => ['binance', 'mexc'],
            'exchange_prices' => [
                'binance' => ['price' => 50000.00, 'data' => null],
                'mexc' => ['price' => 50000.00, 'data' => null],
            ],
        ]);
        
        $event = new CryptoPriceUpdated($cryptoPrice);
        $data = $event->broadcastWith();
        
        $this->assertEquals($cryptoPrice->id, $data['id']);
        $this->assertEquals('BTCUSDT', $data['pair']);
        $this->assertEquals(50000.00, $data['price']);
        $this->assertEquals(49000.00, $data['previous_price']);
        $this->assertEquals(1000.00, $data['price_change']);
        $this->assertEquals(2.04, $data['price_change_percentage']);
        $this->assertEquals(['binance', 'mexc'], $data['exchanges']);
        $this->assertTrue($data['is_increasing']);
        $this->assertEquals($cryptoPrice->updated_at->toIso8601String(), $data['updated_at']);
    }
} 