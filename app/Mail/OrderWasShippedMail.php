<?php

namespace App\Mail;

use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderWasShippedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject = 'Lair.GG Shipped Your Order';

    protected $order;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->order->load('product');
        $params = [
            'username' => $this->order->user->username,
            'order' => $this->order,
        ];
        return $this->subject($this->subject)->markdown('emails.order_is_shipping', $params);
    }
}
