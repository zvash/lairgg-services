<?php

namespace App\Listeners;

use App\Events\OrderStatusWasChangedToShipped;
use App\Mail\OrderWasShippedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderWasShippedEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderStatusWasChangedToShipped  $event
     * @return void
     */
    public function handle(OrderStatusWasChangedToShipped $event)
    {
        $order = $event->order;
        Mail::to($order->user->email)->send(new OrderWasShippedMail($order));

    }
}
