<?php

namespace App\Events;

use App\CashOut;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CashoutStatusWasChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var CashOut $cashOut
     */
    public $cashOut;

    /**
     * CashoutStatusWasChanged constructor.
     * @param CashOut $cashOut
     */
    public function __construct(CashOut $cashOut)
    {
        $this->cashOut = $cashOut;
    }
}
