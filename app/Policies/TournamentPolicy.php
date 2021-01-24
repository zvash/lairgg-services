<?php

namespace App\Policies;

use App\StaffType;
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
     * @param  \App\User $user
     * @param Tournament $tournament
     * @return mixed
     */
    public function update(User $user, Tournament $tournament)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin'])
            ->pluck('id')
            ->all();

        $isAdminOrModerator = $tournament
            ->organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count();
        return $isAdminOrModerator
            ? Response::allow()
            : Response::deny('You do not have administrative access to edit this tournament');
    }

}
