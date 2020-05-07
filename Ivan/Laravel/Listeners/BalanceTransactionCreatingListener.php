<?php

namespace App\Listeners;

use App\Models\Balance;
use App\Models\BalanceTransaction;
use App\Exceptions\BalanceNotEnoughException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BalanceTransactionCreatingListener
{
    /**
     * @param BalanceTransaction $transaction
     * @return void
     */
    public function handle(BalanceTransaction $transaction)
    {
        if (empty($transaction->owner->balance)) {
            $balance = new Balance;
            $balance->owner()->associate($transaction->owner);
            $balance->save();
        }

        \DB::transaction(function () use (&$transaction) {
            /** @var Balance $balance */
            $balance = $transaction->owner->balance()
                ->getQuery()
                ->lockForUpdate()
                ->get()
                ->first();

            $transaction->balance()->associate($balance);
            $transaction->before = $balance->value;

            $balance->value += $transaction->change;

            if ($balance->value < 0) {
                throw new BalanceNotEnoughException;
            }

            $balance->save();

            $transaction->after = $balance->value;
        });
    }
}
