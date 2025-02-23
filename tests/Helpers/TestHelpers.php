<?php

namespace Tests\Helpers;

trait TestHelpers
{
    public function assertArrayHasKeys(array $keys, array $array)
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    public function generateTestPriceData(): array
    {
        return [
            'BTC' => [
                'binance' => [
                    'symbol' => 'BTC',
                    'price' => 50000,
                    'high' => 51000,
                    'low' => 49000,
                    'volume' => 12345,
                    'priceChange' => 500,
                    'priceChangePercent' => 1.01,
                ]
            ]
        ];
    }
}