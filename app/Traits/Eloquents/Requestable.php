<?php

namespace App\Traits\Eloquents;



use App\OrderRequest;

trait Requestable
{
    /**
     * Get all of the resource's transactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function requests()
    {
        return $this->morphMany(OrderRequest::class, 'requestable');
    }
}
