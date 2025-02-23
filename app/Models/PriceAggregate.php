<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAggregate extends Model
{
    protected $fillable = [
        'pair_id',
        'average_price',
        'high_price',
        'low_price',
        'price_change',
        'price_change_percent',
        'volume',
        'number_of_sources',
        'exchange_ids',
        'exchange_data',
        'calculated_at'
    ];

    protected $casts = [
        'average_price' => 'decimal:8',
        'high_price' => 'decimal:8',
        'low_price' => 'decimal:8',
        'price_change' => 'decimal:8',
        'price_change_percent' => 'decimal:4',
        'volume' => 'decimal:8',
        'exchange_ids' => 'json',
        'exchange_data' => 'json',
        'calculated_at' => 'datetime'
    ];

    public function pair(): BelongsTo
    {
        return $this->belongsTo(CryptoPair::class, 'pair_id');
    }

    public function getFormattedPriceChangeAttribute(): string
    {
        return ($this->price_change >= 0 ? '+' : '') . number_format($this->price_change, 8);
    }

    public function getFormattedPriceChangePercentAttribute(): string
    {
        return ($this->price_change_percent >= 0 ? '+' : '') . number_format($this->price_change_percent, 2) . '%';
    }

    public function getExchangeDetailsAttribute(): array
    {
        return collect($this->exchange_data)->mapWithKeys(function ($data) {
            return [$data['name'] => [
                'price' => number_format($data['price'], 8),
                'volume' => number_format($data['volume'], 2),
                'change' => number_format($data['change'], 8),
                'change_percent' => number_format($data['change_percent'], 2) . '%'
            ]];
        })->toArray();
    }
}