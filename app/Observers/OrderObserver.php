<?php

namespace App\Observers;

use App\Order;
use App\OrderRequest;

class OrderObserver
{
    /**
     * Handle the order "created" event.
     *
     * @param \App\Order $order
     * @return void
     */
    public function created(Order $order)
    {
        $params = [
            'user_id' => $order->user_id,
            'requestable_type' => Order::class,
            'requestable_id' => $order->id,
        ];
        OrderRequest::query()->create($params);
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param \App\Order $order
     * @return void
     */
    public function deleted(Order $order)
    {
        OrderRequest::query()
            ->where('requestable_type', Order::class)
            ->where('requestable_id', $order->id)
            ->delete();
    }

    /**
     * Handle the order "force deleted" event.
     *
     * @param \App\Order $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        OrderRequest::query()
            ->where('requestable_type', Order::class)
            ->where('requestable_id', $order->id)
            ->delete();
    }
}