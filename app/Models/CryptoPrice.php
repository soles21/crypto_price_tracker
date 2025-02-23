<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoPrice extends Model
{
    protected $fillable = [
        'pair_id',
        'exchange_id',
        'price',
        'high',
        'low',
        'volume',
        'price_change',
        'price_change_percent',
        'fetched_at',
        'is_valid',
        'raw_data'
    ];

    protected $casts = [
        'price' => 'decimal:8',
        'high' => 'decimal:8',
        'low' => 'decimal:8',
        'volume' => 'decimal:8',
        'price_change' => 'decimal:8',
        'price_change_percent' => 'decimal:4',
        'fetched_at' => 'datetime',
        'is_valid' => 'boolean',
        'raw_data' => 'json'
    ];

    public function pair(): BelongsTo
    {
        return $this->belongsTo(CryptoPair::class, 'pair_id');
    }

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(CryptoExchange::class, 'exchange_id');
    }
}