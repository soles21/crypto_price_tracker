<?php

namespace App\Console\Commands;

use App\Services\CryptoPriceAggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchCryptoPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:fetch-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch cryptocurrency prices from configured exchanges';

    /**
     * Execute the console command.
     */
    public function handle(CryptoPriceAggregator $aggregator): int
    {
        $this->info('Fetching cryptocurrency prices...');
        
        try {
            $aggregator->dispatchPriceFetchJobs();
            $this->info('Price fetch jobs dispatched successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error dispatching price fetch jobs: ' . $e->getMessage());
            Log::error('Error dispatching price fetch jobs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}