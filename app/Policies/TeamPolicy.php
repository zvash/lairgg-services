<?php

namespace App\Policies;

use App\Team;
use App\User;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Team $team
     * @return Response
     */
    public function canInviteParticipant(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny('Only captains can invite new members');
    }

    /**
     * @param User $user
     * @param Team $team
     * @return Response
     */
    public function canUpdate(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny('Only captains can update the team');
    }

    /**
     * @param User $user
     * @param Team $team
     * @return bool
     */
    private function userIsCaptain(User $user, Team $team)
    {
        return in_array(
            $user->id,
            $team
                ->players()
                ->wherePivot('captain', true)
                ->get()
                ->pluck('user_id')
                ->all()
        );
    }
}
