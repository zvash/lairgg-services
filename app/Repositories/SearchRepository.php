<?php

namespace App\Repositories;


use App\Enums\ParticipantAcceptanceState;
use App\Lobby;
use App\Match;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class SearchRepository extends BaseRepository
{
    /**
     * @param string $query
     * @return array
     */
    public function search(string $query)
    {
        if (strlen(str_replace(' ', '', $query)) < 3) {
            return [
                'tournaments' => null,
                'teams' => null,
                'users' => null,
            ];
        }
        return [
            'tournaments' => $this->searchInTournaments($query)->toArray(),
            'teams' => $this->searchInTeams($query)->toArray(),
            'users' => $this->searchInUsers($query)->toArray(),
        ];
    }

    /**
     * @param string $query
     * @return mixed
     */
    public function searchInTournaments(string $query)
    {
        return Tournament::where('title', 'like', "%$query%")->get();
    }

    /**
     * @param string $query
     * @return mixed
     */
    public function searchInTeams(string $query)
    {
        return Team::where('title', 'like', "%$query%")->get();
    }

    /**
     * @param string $query
     * @return mixed
     */
    public function searchInUsers(string $query)
    {
        return User::where('username', 'like', "%$query%")->get();
    }

}