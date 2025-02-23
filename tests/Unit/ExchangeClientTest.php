<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Crypto\Exchanges\BinanceExchange;
use App\Services\Crypto\Exchanges\MexcExchange;
use App\Services\Crypto\Exchanges\HuobiExchange;
use App\Exceptions\ExchangeException;
use ReflectionClass;

class ExchangeClientTest extends TestCase
{
    /** @test */
    public function it_validates_supported_pairs()
    {
        $exchanges = [
            new BinanceExchange(),
            new MexcExchange(),
            new HuobiExchange(),
        ];

        foreach ($exchanges as $exchange) {
            $this->assertTrue($exchange->isSymbolSupported('BTCUSDT'));
            $this->assertTrue($exchange->isSymbolSupported('BTCUSDC'));
            $this->assertTrue($exchange->isSymbolSupported('ETHBTC'));
            $this->assertFalse($exchange->isSymbolSupported('INVALIDPAIR'));
        }
    }

    /** @test */
    public function it_normalizes_symbols_correctly()
    {
        $exchange = new BinanceExchange();
        
        // Use reflection to access protected method
        $reflection = new ReflectionClass($exchange);
        $method = $reflection->getMethod('normalizeSymbol');
        $method->setAccessible(true);

        $testCases = [
            'BTC/USDT' => 'BTCUSDT',
            'btc-usdt' => 'BTCUSDT',
            'BTC_USDT' => 'BTCUSDT',
            'btcusdt' => 'BTCUSDT',
        ];

        foreach ($testCases as $input => $expected) {
            $this->assertEquals($expected, $method->invoke($exchange, $input));
        }
    }

    /** @test */
    public function it_returns_properly_formatted_price_data()
    {
        $exchange = new BinanceExchange();
        $price = $exchange->getPrice('BTCUSDT');

        $this->assertArrayHasKeys([
            'symbol',
            'price',
            'high',
            'low',
            'volume',
            'priceChange',
            'priceChangePercent',
            'timestamp',
            'exchange'
        ], $price);

        $this->assertEquals('BTCUSDT', $price['symbol']);
        $this->assertIsFloat($price['price']);
        $this->assertIsFloat($price['high']);
        $this->assertIsFloat($price['low']);
        $this->assertIsFloat($price['volume']);
        $this->assertIsFloat($price['priceChange']);
        $this->assertIsFloat($price['priceChangePercent']);
        $this->assertIsInt($price['timestamp']);
        $this->assertEquals('binance', $price['exchange']);
    }

    /** @test */
    public function it_handles_errors_properly()
    {
        $exchange = new BinanceExchange();

        $this->expectException(ExchangeException::class);
        $exchange->getPrice('INVALIDPAIR');
    }
}