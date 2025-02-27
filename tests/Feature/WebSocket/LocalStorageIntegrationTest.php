<?php

namespace Tests\Feature\WebSocket;

use App\Events\CryptoPriceUpdated;
use App\Models\CryptoPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalStorageIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the app.blade.php layout properly includes the event handling script.
     */
    public function test_layout_includes_event_handling_script(): void
    {
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        
        $response->assertSee('function storePriceUpdate(data)', false);
        $response->assertSee("localStorage.setItem('crypto_prices',", false);
        $response->assertSee("document.dispatchEvent(new CustomEvent('crypto-price-updated'", false);
    }
    
    /**
     * Test that the WebSocket channel and event name are correctly configured.
     */
    public function test_websocket_channel_and_event_are_correctly_configured(): void
    {
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        
        $response->assertSee("Echo.channel('crypto-prices')", false);
        $response->assertSee("listen('.crypto.price.updated'", false);
    }
    
    /**
     * Test that the price card component has the correct JavaScript to check localStorage.
     */
    public function test_price_card_component_checks_localstorage(): void
    {
        $cryptoPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'exchanges' => ['binance'],
        ]);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        
        $response->assertSee('checkForUpdates: function()', false);
        $response->assertSee("JSON.parse(localStorage.getItem('crypto_prices')", false);
        $response->assertSee("document.addEventListener('crypto-price-updated'", false);
    }
    
    /**
     * Test that all components have cleanup functions to prevent memory leaks.
     */
    public function test_components_have_cleanup_functions(): void
    {
        $cryptoPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'exchanges' => ['binance'],
        ]);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        
        $response->assertSee('cleanup: function()', false);
        $response->assertSee('clearInterval(this.pollIntervalId)', false);
    }
} 