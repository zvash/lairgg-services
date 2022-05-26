<?php

namespace App\Listeners;

use App\Enums\ParticipantAcceptanceState;
use App\Events\ParticipantStatusWasUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyParticipantJoinRequestWasAccepted implements ShouldQueue
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
     * @param  ParticipantStatusWasUpdated  $event
     * @return void
     */
    public function handle(ParticipantStatusWasUpdated $event)
    {
        $participant = $event->participant;
        if (!in_array($participant->status, [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])) {
            return;
        }
        
    }
}
