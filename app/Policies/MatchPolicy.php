<?php

namespace App\Policies;

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
}
