<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coin;
use App\Jobs\ScrapeCoinData;
use Carbon\Carbon;

class ScrapeCoins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:coins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape coin data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coins = Coin::all();
        $now = Carbon::now();

        foreach ($coins as $coin) {
            $firstHistoricalData = Carbon::parse($coin->first_historical_data);

            // Iterate from the first historical data date to the current date
            for ($date = $firstHistoricalData; $date <= $now; $date->addDay()) {
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
                ScrapeCoinData::dispatch($coin, $startDate, $endDate);
            }
        }
    }
}
