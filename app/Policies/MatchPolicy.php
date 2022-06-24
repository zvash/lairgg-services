<?php

namespace App\Policies;

use App\Team;
use App\User;
use App\Match;
use App\StaffType;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class MatchPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Match $match
     * @return Response
     */
    public function setPlayCount(User $user, Match $match)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin', 'Moderator'])
            ->pluck('id')
            ->all();

        $isAdminOrModerator = $match->tournament
            ->organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count();
        if (!$isAdminOrModerator) {
            return Response::deny(__('strings.policy.tournament_edit_access'));
        }

        if ($match->matchHasStarted()) {
            return Response::deny(__('strings.policy.started_tournament_match_edit_access'));
        }

        return Response::allow();
    }

    /**
     * @param User $user
     * @param Match $match
     * @return Response
     */
    public function viewDisputes(User $user, Match $match)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin', 'Moderator'])
            ->pluck('id')
            ->all();

        $isAdminOrModerator = $match->tournament
            ->organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count();
        if (!$isAdminOrModerator) {
            return Response::deny(__('strings.policy.tournament_edit_access'));
        }

        if ($match->matchHasStarted()) {
            return Response::deny(__('strings.policy.started_tournament_match_edit_access'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can set ready for the match.
     *
     * @param \App\User $user
     * @param Match $match
     * @return mixed
     */
    public function setReady(User $user, Match $match)
    {
        $tournament = $match->tournament;
        if ($this->isAdminOrModeratorOfTournament($user, $tournament)) {
            return Response::allow();
        } else if ($this->userIsCaptainOfTheMatchParty($user, $match)) {
            return Response::allow();
        }
        return Response::deny(__('strings.policy.match_set_ready_access'));
    }

    /**
     * @param User $user
     * @param Match $match
     * @return bool
     */
    private function userIsCaptainOfTheMatchParty(User $user, Match $match)
    {
        $participants = $match->getParticipants();
        foreach ($participants as $participant) {
            if ($participant->participantable_type == User::class) {
                if ($participant->participantable_id == $user->id) {
                    return true;
                }
            } else if ($participant->participantable_type == Team::class) {
                $participantable = $participant->participantable;
                $userIsCaptain = $participantable->players()
                        ->where('user_id', $user->id)
                        ->where('captain', 1)
                        ->count() > 0;
                if ($userIsCaptain) {
                    return true;
                }
            }
        }
        return false;
    }
}
