<?php

namespace App\Events;

use App\Participant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantJoinRequestWasAccepted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Participant $participant
     */
    public $participant;

    /**
     * ParticipantJoinRequestWasAccepted constructor.
     * @param Participant $participant
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }


}
