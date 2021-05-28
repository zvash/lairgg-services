<?php

namespace App\Traits\Eloquents;

use App\Invitation;

trait InviteAware
{
    /**
     * Get all of the resource invitation requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function invitations()
    {
        return $this->morphMany(Invitation::class, 'invite_aware');
    }
}
