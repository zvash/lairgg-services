<?php

namespace App\Policies;

use App\Play;
use App\Team;
use App\User;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlayPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User $user
     * @param  \App\Play $play
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
        } else if ($this->userIsATeamMemberOfTheMatchParty($user, $play)) {
            return Response::allow();
        }
        return Response::deny('You are not authorized to update this play.');
    }

    private function userIsATeamMemberOfTheMatchParty(User $user, Play $play)
    {
        $teamIds = $play->parties->pluck('team_id')->all();
        return Team::whereIn('id', $teamIds)
                ->whereHas('players', function ($players) use ($user) {
                    return $players->where('user_id', $user->id);
                })->count() > 0;
    }
}
