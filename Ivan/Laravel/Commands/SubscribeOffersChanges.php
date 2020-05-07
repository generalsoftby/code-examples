<?php

namespace App\Console\Commands;

use App\Models\Offer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SubscribeOffersChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:changes:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to offers changes redis channel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $redis = Redis::connection('publisher');

        $redis->subscribe(['Offers.Changes'], function ($message) {
            $data = json_decode($message);

            /** @var Offer $offer */
            $offer = isset($data->offerId) ?
                Offer::query()->find($data->offerId) :
                Offer::query()->where('tradeofferid', $data->tradeofferid)->first();

            try {
                isset($data->changes->tradeofferid) && $offer->tradeofferid = $data->changes->tradeofferid;
                isset($data->changes->tradeid) && $offer->tradeid = $data->changes->tradeid;
                isset($data->changes->trade_offer_state) && $offer->trade_offer_state = $data->changes->trade_offer_state;
                isset($data->changes->message) && $offer->message = $data->changes->message;
            } catch (\Exception $e) {
                $this->warn('Unknown offers changes (' . $message . ')');
                logger()->error($e);
                return;
            }

            $offer->save();
        });
    }
}
