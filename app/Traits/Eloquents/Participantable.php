<?php

namespace App\Traits\Eloquents;

use App\Participant;

trait Participantable
{
    /**
     * Get all of the resource participants.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function participants()
    {
        return $this->morphMany(Participant::class, 'participantable');
    }
}
