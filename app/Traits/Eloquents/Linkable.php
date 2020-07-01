<?php

namespace App\Traits\Eloquents;

use App\Link;

trait Linkable
{
    /**
     * Get all of the resource's links.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function links()
    {
        return $this->morphMany(Link::class, 'linkable');
    }
}
