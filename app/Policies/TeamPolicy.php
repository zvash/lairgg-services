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
            : Response::deny(__('strings.policy.team_invite_access'));
    }

    /**
     * @param User $user
     * @param Team $team
     * @return Response
     */
    public function canShareGem(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny(__('strings.policy.team_share_gem_access'));
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
            : Response::deny(__('strings.policy.team_remove_member_access'));
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
            : Response::deny(__('strings.policy.team_delete_access'));
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
            : Response::deny(__('strings.policy.team_promote_member_access'));
    }

    public function canInvite(User $user, Team $team)
    {
        return $this->userIsCaptain($user, $team)
            ? Response::allow()
            : Response::deny(__('strings.policy.team_invite_access'));
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
            : Response::deny(__('strings.policy.team_edit_access'));
    }

    public function canAccessJoinUrl(User $user, Team $team)
    {
        return $this->userIdBelongsToTeam($user->id, $team)
            ? Response::allow()
            : Response::deny(__('strings.policy.team_join_url_view_access'));
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
            : Response::deny(__('strings.policy.team_join_url_edit_access'));
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
            : Response::deny(__('strings.policy.team_cancel_invitation_access'));
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
