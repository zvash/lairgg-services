<?php

namespace App\Listeners;

use App\Enums\ParticipantAcceptanceState;
use App\Events\ParticipantStatusWasUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetCheckedInFalseForRejectedParticipant implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  ParticipantStatusWasUpdated  $event
     * @return void
     */
    public function handle(ParticipantStatusWasUpdated $event)
    {
        $participant = $event->participant;
        if ($participant->status != ParticipantAcceptanceState::REJECTED) {
            return;
        }
        $participant->setAttribute('checked_in_at', null)->save();
    }
}
