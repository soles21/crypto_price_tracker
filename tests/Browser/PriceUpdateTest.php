<?php

namespace Tests\Browser;

use App\Events\CryptoPriceUpdated;
use App\Models\CryptoPrice;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PriceUpdateTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that prices are displayed correctly on initial load.
     */
    public function test_prices_are_displayed_on_initial_load(): void
    {
        $btcPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'previous_price' => 49000.00,
            'price_change' => 1000.00,
            'price_change_percentage' => 2.04,
            'exchanges' => ['binance', 'mexc'],
        ]);
        
        $ethPrice = CryptoPrice::factory()->create([
            'pair' => 'ETHUSDT',
            'price' => 3000.00,
            'previous_price' => 2900.00,
            'price_change' => 100.00,
            'price_change_percentage' => 3.45,
            'exchanges' => ['binance', 'mexc'],
        ]);
        
        $this->browse(function (Browser $browser) use ($btcPrice, $ethPrice) {
            $browser->visit('/dashboard')
                ->waitForText('BTCUSDT')
                ->assertSee('BTCUSDT')
                ->assertSee('ETHUSDT')
                ->assertSee('50,000.00')
                ->assertSee('3,000.00');
        });
    }
    
    /**
     * Test that the UI updates when new price data is inserted into localStorage.
     */
    public function test_ui_updates_when_new_price_data_is_inserted_into_localstorage(): void
    {
        $btcPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'previous_price' => 49000.00,
            'price_change' => 1000.00,
            'price_change_percentage' => 2.04,
            'exchanges' => ['binance', 'mexc'],
        ]);
        
        $this->browse(function (Browser $browser) use ($btcPrice) {
            $browser->visit('/dashboard')
                ->waitForText('BTCUSDT')
                ->assertSee('50,000.00')
        
                ->script([
                    'const priceData = {
                        BTCUSDT: {
                            price: 52000.00,
                            price_change_percentage: 4.0,
                            is_increasing: true,
                            exchanges: ["binance", "mexc"],
                            updated_at: new Date().toISOString()
                        }
                    };
                    localStorage.setItem("crypto_prices", JSON.stringify(priceData));
                    document.dispatchEvent(new CustomEvent("crypto-price-updated", { 
                        detail: { pair: "BTCUSDT", data: priceData.BTCUSDT }
                    }));'
                ])
                ->pause(3000)
                ->assertSee('52,000.00');
        });
    }
    
    /**
     * Test that the price cards show visual indicators when prices change.
     */
    public function test_price_cards_show_visual_indicators_when_prices_change(): void
    {
        $btcPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'previous_price' => 49000.00,
            'price_change' => 1000.00,
            'price_change_percentage' => 2.04,
            'exchanges' => ['binance', 'mexc'],
        ]);
        
        $this->browse(function (Browser $browser) use ($btcPrice) {
            $browser->visit('/dashboard')
                ->waitForText('BTCUSDT')
                // Check initial state (no highlight)
                ->assertMissing('.wobble-animation') 
                // Simulate a price update via localStorage
                ->script([
                    'const priceData = {
                        BTCUSDT: {
                            price: 52000.00,
                            price_change_percentage: 4.0,
                            is_increasing: true,
                            exchanges: ["binance", "mexc"],
                            updated_at: new Date().toISOString()
                        }
                    };
                    localStorage.setItem("crypto_prices", JSON.stringify(priceData));
                    document.dispatchEvent(new CustomEvent("crypto-price-updated", { 
                        detail: { pair: "BTCUSDT", data: priceData.BTCUSDT }
                    }));'
                ])
                ->pause(1000) // Wait for animation to start
                ->assertPresent('.wobble-animation') // Check for animation
                ->pause(3000) // Wait for animation to end
                ->assertMissing('.wobble-animation'); // Animation should be gone
        });
    }
    
    /**
     * Test that the refresh button manually updates prices.
     */
    public function test_refresh_button_manually_updates_prices(): void
    {
        $btcPrice = CryptoPrice::factory()->create([
            'pair' => 'BTCUSDT',
            'price' => 50000.00,
            'previous_price' => 49000.00,
            'price_change' => 1000.00,
            'price_change_percentage' => 2.04,
            'exchanges' => ['binance', 'mexc'],
        ]);
        
        // Create a new price record that would be returned after refresh
        $btcPrice->price = 51000.00;
        $btcPrice->save();
        
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->waitForText('BTCUSDT')
                ->assertSee('50,000.00')
                ->press('Refresh Prices')
                ->waitForText('Refreshing')
                ->waitUntilMissing('Refreshing')
                ->assertSee('51,000.00');
        });
    }
} 