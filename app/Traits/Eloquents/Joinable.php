<?php

namespace App\Traits\Eloquents;

use App\Join;

trait Joinable
{
    /**
     * Get all of the resource join requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function joins()
    {
        return $this->morphMany(Join::class, 'joinable');
    }
}
