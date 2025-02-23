<?php

namespace App\Services\Crypto\Exchanges;

use App\Exceptions\ExchangeException;

class ExchangeFactory
{
    public static function create(string $exchangeName): ExchangeClientInterface
    {
        return match (strtolower($exchangeName)) {
            'binance' => new BinanceExchange(),
            'mexc' => new MexcExchange(),
            'huobi' => new HuobiExchange(),
            default => throw new ExchangeException("Unsupported exchange: {$exchangeName}")
        };
    }
}