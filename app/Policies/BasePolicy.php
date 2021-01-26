<?php

namespace App\Policies;

use App\User;
use App\StaffType;
use App\Tournament;

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

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return bool
     */
    protected function isAdminOrModeratorOfTournament(User $user, Tournament $tournament)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin', 'Moderator'])
            ->pluck('id')
            ->all();
        return $tournament
            ->organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count() > 0;

    }
}