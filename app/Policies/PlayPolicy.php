<?php

namespace App\Policies;

use App\Participant;
use App\Play;
use App\Team;
use App\User;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

class PlayPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\User $user
     * @param \App\Play $play
     * @return mixed
     */
    public function update(User $user, Play $play)
    {
        $tournament = $play->match->tournament;
        if ($this->isAdminOrModeratorOfTournament($user, $tournament)) {
            return Response::allow();
        } else if ($this->userIsCaptainOfTheMatchParty($user, $play)) {
            return Response::allow();
        }
        return Response::deny(__('strings.policy.play_edit_access'));
    }

    /**
     * @param User $user
     * @param Play $play
     * @return bool
     */
    private function userIsCaptainOfTheMatchParty(User $user, Play $play)
    {
        $participants = $play->match->getParticipants();
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
