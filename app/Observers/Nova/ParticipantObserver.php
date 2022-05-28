<?php

namespace App\Observers\Nova;

use App\Events\ParticipantStatusWasUpdated;
use App\Participant;

class ParticipantObserver
{

    /**
     * Handle the participant "updating" event.
     *
     * @param  \App\Participant  $participant
     * @return void
     */
    public function updating(Participant $participant)
    {
        if ($participant->isDirty('status')) {
            event(new ParticipantStatusWasUpdated($participant, $participant->getOriginal('status')));
        }
    }
}
