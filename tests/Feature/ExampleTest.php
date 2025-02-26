<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\CryptoPrice;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        Artisan::call('migrate');
        
        CryptoPrice::create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'previous_price' => 49500.00,
            'price_change' => 500.00,
            'price_change_percentage' => 1.01,
            'exchange_prices' => [
                'binance' => [
                    'price' => 50000.00,
                    'data' => null,
                ]
            ],
            'exchanges' => ['binance'],
        ]);
    }
    
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}