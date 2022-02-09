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
        $match = $play->match;
        if ($match->winner_team_id) {
            return Response::deny('Match is over.');
        }
        $tournament = $play->match->tournament;
        if ($this->isAdminOrModeratorOfTournament($user, $tournament)) {
            return Response::allow();
        } else if ($this->userIsCaptainOfTheMatchParty($user, $play)) {
            return Response::allow();
        }
        return Response::deny('You are not authorized to update this play.');
    }

    /**
     * @param User $user
     * @param Play $play
     * @return bool
     */
    private function userIsCaptainOfTheMatchParty(User $user, Play $play)
    {
        $participantIds = $play->parties->pluck('team_id')->all();
        $participantIds[] = 0;
        $participant = Participant::query()
            ->whereIn('id', $participantIds)
            ->where(function (Builder $query) use ($user) {
                return $query->where('participantable_type', User::class)
                    ->where('participantable_id', $user->id);
            })->orWhere(function (Builder $query) use ($user) {
                $teamIds = $user->teams()->pluck('teams.id');
                $teamIds[] = 0;
                return $query->where('participantable_type', Team::class)
                    ->whereIn('participantable_id', $teamIds);
            })->first();

        if (!$participant) {
            return false;
        }
        if ($participant->participantable_type == User::class) {
            return true;
        }
        $participantable = $participant->participantable;
        return $participantable->players()
                ->where('user_id', $user->id)
                ->where('captain', 1)
                ->count() > 0;
    }
}
