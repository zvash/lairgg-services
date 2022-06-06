<?php

namespace App\Policies;

use App\User;
use App\Dispute;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisputePolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Dispute $dispute
     * @return Response
     */
    public function closeDispute(User $user, Dispute $dispute)
    {
        $tournament = $dispute->play->match->tournament;
        if ($this->isAdminOrModeratorOfTournament($user, $tournament)) {
            return Response::allow();
        }
        return Response::deny(__('strings.policy.dispute_close_access'));
    }
}
