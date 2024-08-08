<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coin;
use App\Jobs\ScrapeCoinData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;


class ScrapeCoinsBatch extends Command
{
    protected $signature = 'scrape:coins-batch';

    protected $description = 'Scrape coin data in batches';
    protected $batchSize = 500; // Adjust the batch size as needed

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coins = Coin::all();
        $now = Carbon::now();

        $jobs = [];

        foreach ($coins as $coin) {
            $firstHistoricalData = Carbon::parse($coin->first_historical_data);

            for ($date = $firstHistoricalData; $date <= $now; $date->addDay()) {
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();

                $jobs[] = new ScrapeCoinData($coin, $startDate, $endDate);

                // Dispatch jobs in batches to avoid memory issues
                if (count($jobs) >= $this->batchSize) {
                    Bus::batch($jobs)->dispatch();
                    $jobs = []; // Reset jobs array
                }
            }
        }

        // Dispatch any remaining jobs
        if (count($jobs) > 0) {
            Bus::batch($jobs)->dispatch();
        }

        $this->info('Batch jobs dispatched.');
    }

}
