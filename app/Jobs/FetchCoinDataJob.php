<?php

namespace App\Jobs;

use App\Models\Coin;
use GuzzleHttp\Client;
use Carbon\Carbon;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchCoinDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('FetchCoinDataJob started');
        $this->fetchCoinMarketCapData();
        $this->fetchLiveCoinWatchData();
        Log::info('FetchCoinDataJob completed');
    }

    private function fetchCoinMarketCapData()
    {
        $client = new Client();
        $response = $client->get('https://s3.coinmarketcap.com/generated/core/crypto/cryptos.json');
        $data  = json_decode($response->getBody()->getContents(), true);

        $fields = $data['fields'];
        $values = $data['values'];

        foreach ($values as $coinData) {
            $coin = array_combine($fields, $coinData);

            Coin::updateOrCreate(
                [
                    'symbol' => $coin['symbol'],
                ],
                [
                    'name' => $coin['name'],
                    'first_historical_data' => Carbon::parse($coin['first_historical_data']),
                ]
            );
        }
    }

    private function fetchLiveCoinWatchData()
    {
        $client = new Client();
        $response = $client->get('https://http-api.livecoinwatch.com/coins?offset=0&limit=400&sort=rank&order=ascending&currency=USD&platforms=');
        $data = json_decode($response->getBody()->getContents(), true);

        foreach ($data['data'] as $coinData) {
            Coin::updateOrCreate(
                [
                    'symbol' => $coinData['code'],
                ],
                [
                    'name' => $coinData['name'],
                    'first_historical_data' => Carbon::parse($coinData['firstSeen']),
                ]
            );
        }
    }
}
