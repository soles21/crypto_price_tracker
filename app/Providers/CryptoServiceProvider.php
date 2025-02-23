<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Crypto\PriceFetcher\PriceFetcherInterface;
use App\Services\Crypto\PriceFetcher\PriceFetcherService;
use App\Services\Crypto\Exchanges\ExchangeFactory;

class CryptoServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the PriceFetcher service
        $this->app->singleton(PriceFetcherInterface::class, function ($app) {
            return new PriceFetcherService();
        });

        // Register the exchange factory
        $this->app->singleton('crypto.exchanges', function ($app) {
            $exchanges = explode(',', config('crypto.exchanges'));
            $clients = [];

            foreach ($exchanges as $exchange) {
                $clients[$exchange] = ExchangeFactory::create($exchange);
            }

            return $clients;
        });
    }

    public function boot()
    {
        //
    }
}