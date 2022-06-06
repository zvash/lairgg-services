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
            : Response::deny(__('strings.policy.tournament_create_access'));
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
        return $this->onlyAdminOfTournament($user, $tournament, __('strings.policy.tournament_edit_access'));
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return Response
     */
    public function createBracket(User $user, Tournament $tournament)
    {
        return $this->onlyAdminOfTournament($user, $tournament, __('strings.policy.tournament_create_bracket_access'));
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return Response
     */
    public function canAddParticipants(User $user, Tournament $tournament)
    {
        return $this->onlyAdminOfTournament($user, $tournament, __('strings.policy.tournament_add_participant_access'));
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return Response
     */
    public function canUpdateParticipantStatus(User $user, Tournament $tournament)
    {
        return $this->onlyAdminOfTournament($user, $tournament, __('strings.policy.tournament_update_participant_status_access'));
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return Response
     */
    public function canInviteParticipant(User $user, Tournament $tournament)
    {
        return $this->onlyAdminOfTournament($user, $tournament, __('strings.policy.tournament_send_invitation_access'));
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @param string $message
     * @return Response
     */
    private function onlyAdminOfTournament(User $user, Tournament $tournament, string $message)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin'])
            ->pluck('id')
            ->all();

        $isAdmin = $tournament
            ->organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count();
        return $isAdmin
            ? Response::allow()
            : Response::deny($message);
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @param string $message
     * @return Response
     */
    private function onlyTournamentStaff(User $user, Tournament $tournament, string $message)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin'])
            ->pluck('id')
            ->all();

        $isAdmin = $tournament
            ->organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count();

        $isModerator = in_array($user->id, $tournament->moderators()->pluck('id')->toArray());

        return $isAdmin || $isModerator
            ? Response::allow()
            : Response::deny($message);
    }

}
