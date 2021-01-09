<?php

namespace App\Policies;

use App\User;

class BasePolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\User  $user
     * @param  string  $ability
     * @return bool
     */
    public function before(User $user, $ability)
    {
        $authorized = in_array($user->email, [
            'hossein@edoramedia.com',
            'ali.shafiee@edoramedia.com',
            'ilyad@edoramedia.com',
            'farbod@edoramedia.com'
        ]);
        return $authorized ? $authorized : null;
    }

    public function __call($name, $arguments)
    {
        // Do nothing
    }
}