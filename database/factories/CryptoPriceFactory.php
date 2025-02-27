<?php

namespace Database\Factories;

use App\Models\CryptoPrice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CryptoPrice>
 */
class CryptoPriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CryptoPrice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 100, 100000);
        $previousPrice = $this->faker->randomFloat(2, 0.9 * $price, 1.1 * $price);
        $priceChange = $price - $previousPrice;
        $priceChangePercentage = ($priceChange / $previousPrice) * 100;
        
        $exchanges = $this->faker->randomElements(['binance', 'mexc', 'huobi'], 
            $this->faker->numberBetween(1, 3));
            
        $exchangePrices = [];
        foreach ($exchanges as $exchange) {
            $exchangePrices[$exchange] = [
                'price' => $price + $this->faker->randomFloat(2, -10, 10),
                'data' => [
                    'last' => (string)($price + $this->faker->randomFloat(2, -10, 10)),
                    'symbol' => 'BTCUSDT',
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            ];
        }
        
        return [
            'pair' => $this->faker->randomElement(['BTCUSDT', 'ETHBTC', 'BTCUSDC']),
            'price' => $price,
            'previous_price' => $previousPrice,
            'price_change' => $priceChange,
            'price_change_percentage' => $priceChangePercentage,
            'exchanges' => $exchanges,
            'exchange_prices' => $exchangePrices,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
    
    /**
     * Indicate that the price is increasing.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function increasing(): Factory
    {
        return $this->state(function (array $attributes) {
            $previousPrice = $attributes['price'] * 0.95;
            $priceChange = $attributes['price'] - $previousPrice;
            $priceChangePercentage = ($priceChange / $previousPrice) * 100;
            
            return [
                'previous_price' => $previousPrice,
                'price_change' => $priceChange,
                'price_change_percentage' => $priceChangePercentage,
            ];
        });
    }
    
    /**
     * Indicate that the price is decreasing.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function decreasing(): Factory
    {
        return $this->state(function (array $attributes) {
            $previousPrice = $attributes['price'] * 1.05;
            $priceChange = $attributes['price'] - $previousPrice;
            $priceChangePercentage = ($priceChange / $previousPrice) * 100;
            
            return [
                'previous_price' => $previousPrice,
                'price_change' => $priceChange,
                'price_change_percentage' => $priceChangePercentage,
            ];
        });
    }
} 