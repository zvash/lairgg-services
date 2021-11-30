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
    public function canRemovePlayer(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny('Only captains can remove a member');
    }

    /**
     * @param User $user
     * @param Team $team
     * @return Response
     */
    public function canDeleteTeam(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny('Only captains can delete their teams');
    }

    /**
     * @param User $user
     * @param Team $team
     * @return Response
     */
    public function canPromoteToCaptain(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny('Only captains can promote members');
    }

    public function canInvite(User $user, Team $team)
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

    public function canAccessJoinUrl(User $user, Team $team)
    {
        return $this->userIdBelongsToTeam($user->id, $team)
            ? Response::allow()
            : Response::deny('Only team members can access to the join url');
    }

    /**
     * @param User $user
     * @param Team $team
     * @return Response
     */
    public function canSetJoinUrl(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny('Only captains can set team\'s join URL');
    }

    /**
     * @param User $user
     * @param Team $team
     * @return Response
     */
    public function canCancelInvitation(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny('Only captain can cancel invitations');
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

    /**
     * @param int $userId
     * @param Team $team
     * @return bool
     */
    private function userIdBelongsToTeam(int $userId, Team $team)
    {
        return in_array(
            $userId,
            $team
                ->players()
                ->get()
                ->pluck('user_id')
                ->all()
        );
    }
}
