<?php

namespace App\Policies;

use App\Participant;
use App\StaffType;
use App\User;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParticipantPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Participant $participant
     * @return Response
     */
    public function checkIn(User $user, Participant $participant)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin', 'Moderator'])
            ->pluck('id')
            ->all();

        $isAdminOrModerator = $participant->tournament
            ->organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count();
        return $isAdminOrModerator
            ? Response::allow()
            : Response::deny(__('strings.policy.tournament_edit_access'));
    }
}
