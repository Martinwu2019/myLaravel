<?php

namespace App\Jobs;

use App\Models\Coin;
use App\Models\CoinPrice;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapeCoinData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;
    protected $coin;
    protected $startDate;
    protected $endDate;

    /**
     * Create a new job instance.
     */
    public function __construct(Coin $coin, $startDate, $endDate)
    {
        $this->coin = $coin;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $client = new Client();
        $response = $client->get('https://http-api.livecoinwatch.com/coins/history/range', [
            'query' => [
                'coin' => $this->coin->symbol,
                'start' => $this->startDate->timestamp * 1000,
                'end' => $this->endDate->timestamp * 1000,
                'currency' => 'USD'
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $coinPrices = [];
        foreach ($data['data'] as $entry) {
            $coinPrices[] = [
                'coin_id' => $this->coin->id,
                'date' => Carbon::createFromTimestampMs($entry['date']),
                'rate' => $entry['rate'],
                'volume' => $entry['volume'],
                'cap' => $entry['cap'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Batch insert
        // Ensure that $coinPrices is not empty before inserting
        if (!empty($coinPrices)) {
            CoinPrice::insert($coinPrices);
        }
    }
}
