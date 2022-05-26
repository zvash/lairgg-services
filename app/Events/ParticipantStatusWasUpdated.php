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

class ParticipantStatusWasUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Participant $participant
     */
    public $participant;

    /**
     * @var string $previousState
     */
    public $previousState;

    /**
     * Create a new event instance.
     *
     * @param Participant $participant
     * @param string|null $previousState
     */
    public function __construct(Participant $participant, ?string $previousState = null)
    {
        $this->participant = $participant;
        $this->previousState = $previousState;
    }
}
