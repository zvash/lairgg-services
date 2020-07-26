<?php

namespace App\Traits\Eloquents;

use App\{
    Transaction,
    User,
    ValueType
};

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

    /**
     * Add transaction to transactionable object.
     *
     * @param  \App\User  $user
     * @param  mixed  $value
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Model|false
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function addTransaction(User $user, $value, string $type)
    {
        $type = ValueType::whereTitle($type)->firstOrFail();

        $transaction = new Transaction(compact('value'));
        $transaction->user()->associate($user);
        $transaction->valueType()->associate($type);

        return $this->transactions()->save($transaction);
    }
}
