<?php

namespace App\Traits\Eloquents;

use App\Transaction;

trait Transactionable
{
    /**
     * Get all of the resource's transactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
