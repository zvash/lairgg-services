<?php

namespace App\Observers;

use App\CashOut;
use App\OrderRequest;

class CashOutObserver
{
    /**
     * Handle the cash out "created" event.
     *
     * @param  \App\CashOut  $cashOut
     * @return void
     */
    public function created(CashOut $cashOut)
    {
        $params = [
            'user_id' => $cashOut->user_id,
            'requestable_type' => CashOut::class,
            'requestable_id' => $cashOut->id,
        ];
        OrderRequest::query()->create($params);
    }

    /**
     * Handle the cash out "deleted" event.
     *
     * @param  \App\CashOut  $cashOut
     * @return void
     */
    public function deleted(CashOut $cashOut)
    {
        OrderRequest::query()
            ->where('requestable_type', CashOut::class)
            ->where('requestable_id', $cashOut->id)
            ->delete();
    }

    /**
     * Handle the cash out "force deleted" event.
     *
     * @param  \App\CashOut  $cashOut
     * @return void
     */
    public function forceDeleted(CashOut $cashOut)
    {
        OrderRequest::query()
            ->where('requestable_type', CashOut::class)
            ->where('requestable_id', $cashOut->id)
            ->delete();
    }
}
