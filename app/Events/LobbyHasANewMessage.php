<?php

namespace App\Events;

use App\Match;
use App\Participant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LobbyHasANewMessage
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
     * @var array $participantIds
     */
    public $participantIds;

    /**
     * LobbyHasANewMessage constructor.
     * @param Match $match
     * @param int $senderUserId
     * @param array $participantIds
     */
    public function __construct(Match $match, int $senderUserId, array $participantIds)
    {
        $this->match = $match;
        $this->participantIds = $participantIds;
        $this->senderUserId = $senderUserId;
    }

}
