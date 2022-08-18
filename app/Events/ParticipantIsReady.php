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

class ParticipantIsReady
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Match $match
     */
    public $match;

    /**
     * @var Participant $participant
     */
    public $participant;

    /**
     * ParticipantIsReady constructor.
     * @param Match $match
     * @param Participant $participant
     */
    public function __construct(Match $match, Participant $participant)
    {
        $this->match = $match;
        $this->participant = $participant;
    }
}
