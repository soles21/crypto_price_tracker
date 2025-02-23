<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Events\CryptoPricesUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CryptoPriceBroadcastTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_broadcasts_price_updates()
    {
        Event::fake();

        $prices = [
            'BTC' => [
                'binance' => [
                    'symbol' => 'BTC',
                    'price' => 50000,
                    'high' => 51000,
                    'low' => 49000,
                    'volume' => 12345,
                    'priceChange' => 500,
                    'priceChangePercent' => 1.01,
                ]
            ]
        ];

        event(new CryptoPricesUpdated($prices));

        Event::assertDispatched(CryptoPricesUpdated::class, function ($event) use ($prices) {
            return $event->prices === $prices;
        });
    }

    /** @test */
    public function test_broadcast_endpoint_works()
    {
        $response = $this->get('/test-broadcast');
        
        $response->assertStatus(200);
        $response->assertSee('Event dispatched!');
    }
}