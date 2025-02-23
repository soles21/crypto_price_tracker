<?php

namespace App\Console\Commands;

use App\Jobs\FetchCryptoPrices;
use Illuminate\Console\Command;

class StartCryptoPriceFetching extends Command
{
    protected $signature = 'crypto:fetch-prices';
    protected $description = 'Start fetching crypto prices';

    public function handle()
    {
        $this->info('Starting crypto price fetching...');
        FetchCryptoPrices::dispatch();
        $this->info('Initial job dispatched. Check the queue worker logs for updates.');
    }
}