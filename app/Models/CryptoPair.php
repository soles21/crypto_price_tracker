<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoPair extends Model
{
    protected $fillable = [
        'symbol',
        'base_currency',
        'quote_currency',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(CryptoPrice::class, 'pair_id');
    }

    public function aggregates(): HasMany
    {
        return $this->hasMany(PriceAggregate::class, 'pair_id');
    }

    public function getLatestAggregate()
    {
        return $this->aggregates()
            ->latest('calculated_at')
            ->first();
    }
}