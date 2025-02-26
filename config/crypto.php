<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Crypto API Key
    |--------------------------------------------------------------------------
    |
    | This is your API key for the FreeCryptoAPI service.
    |
    */
    'api_key' => env('CRYPTO_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Cryptocurrency Pairs
    |--------------------------------------------------------------------------
    |
    | List of cryptocurrency pairs to track.
    |
    */
    'pairs' => array_filter(explode(',', env('CRYPTO_PAIRS', 'BTCUSDC,BTCUSDT,ETHBTC'))),

    /*
    |--------------------------------------------------------------------------
    | Exchanges
    |--------------------------------------------------------------------------
    |
    | List of exchanges to fetch prices from.
    |
    */
    'exchanges' => array_filter(explode(',', env('CRYPTO_EXCHANGES', 'binance,mexc,huobi'))),
    
    /*
    |--------------------------------------------------------------------------
    | Fetch Interval
    |--------------------------------------------------------------------------
    |
    | Interval in seconds between price fetches.
    |
    */
    'fetch_interval' => (int) env('CRYPTO_FETCH_INTERVAL', 5),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | URLs for the FreeCryptoAPI service.
    |
    */
    'api_url' => 'https://api.freecryptoapi.com/v1',
    
    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long to cache API responses in seconds.
    |
    */
    'cache_ttl' => 60, // 1 minute
];