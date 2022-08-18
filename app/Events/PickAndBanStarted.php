<?php

namespace App\Events;

use App\Match;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PickAndBanStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Match $match
     */
    public $match;

    /**
     * PickAndBanStarted constructor.
     * @param Match $match
     */
    public function __construct(Match $match)
    {
        $this->match = $match;
    }


}
