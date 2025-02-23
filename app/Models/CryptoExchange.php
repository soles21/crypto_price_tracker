<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoExchange extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'last_fetch_at',
        'last_successful_fetch_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_fetch_at' => 'datetime',
        'last_successful_fetch_at' => 'datetime'
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(CryptoPrice::class, 'exchange_id');
    }
}