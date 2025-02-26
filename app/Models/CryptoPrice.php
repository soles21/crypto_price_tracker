<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoPrice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pair',
        'price',
        'previous_price',
        'price_change',
        'price_change_percentage',
        'exchange_prices',
        'exchanges',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:8',
        'previous_price' => 'decimal:8',
        'price_change' => 'decimal:8',
        'price_change_percentage' => 'decimal:4',
        'exchange_prices' => 'array',
        'exchanges' => 'array',
    ];

    /**
     * Calculate and update the price change data.
     *
     * @param float $newPrice
     * @return void
     */
    public function updatePriceChange(float $newPrice): void
    {
        if ($this->price) {
            $this->previous_price = $this->price;
            $this->price_change = $newPrice - $this->previous_price;
            
            if ($this->previous_price > 0) {
                $this->price_change_percentage = ($this->price_change / $this->previous_price) * 100;
            } else {
                $this->price_change_percentage = 0;
            }
        }
        
        $this->price = $newPrice;
    }

    /**
     * Check if price is increasing.
     *
     * @return bool|null
     */
    public function isPriceIncreasing(): ?bool
    {
        if ($this->price_change === null) {
            return null;
        }
        
        return $this->price_change > 0;
    }
}