<?php

namespace App\Traits\Eloquents;

use App\Follower;

trait Followable
{
    /**
     * Get all of the resource's followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function followers()
    {
        return $this->morphMany(Follower::class, 'followable');
    }
}
