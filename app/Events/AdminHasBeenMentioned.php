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

class AdminHasBeenMentioned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Match $match
     */
    public $match;

    /**
     * @var int $senderUserId
     */
    public $senderUserId;

    /**
     * @var array $staffIds
     */
    public $staffIds;

    /**
     * LobbyHasANewMessage constructor.
     * @param Match $match
     * @param int $senderUserId
     * @param array $staffIds
     */
    public function __construct(Match $match, int $senderUserId, array $staffIds)
    {
        $this->match = $match;
        $this->staffIds = $staffIds;
        $this->senderUserId = $senderUserId;
    }
}
