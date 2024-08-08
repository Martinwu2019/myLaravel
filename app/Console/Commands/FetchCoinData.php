<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchCoinDataJob;
use Illuminate\Support\Facades\Log;

class FetchCoinData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:coin-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store coin data from CoinMarketCap and LiveCoinWatch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Dispatching FetchCoinDataJob');
        FetchCoinDataJob::dispatch();
        Log::info('FetchCoinDataJob dispatched.');
    }
}
