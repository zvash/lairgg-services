<?php

namespace App\Policies;

use App\User;
use App\Tournament;
use App\Organization;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User $user
     * @param Organization $organization
     * @return mixed
     */
    public function createByOrganization(User $user, Organization $organization)
    {
        $userIsMemberOfOrganization = $organization
            ->staff()
            ->where('user_id', $user->id)
            ->count();
        return $userIsMemberOfOrganization
            ? Response::allow()
            : Response::deny('You are not a member of this organization.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Tournament  $tournament
     * @return mixed
     */
    public function update(User $user, Tournament $tournament)
    {
        return true;
    }


}
