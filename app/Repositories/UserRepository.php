<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Match;
use App\Participant;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository
{
    public $modelClass = User::class;

    /**
     * @param User $user
     * @param string $status
     * @param int $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getUserOrders(User $user, string $status = 'all', int $paginate = 10)
    {
        $statusMap = [
            'preparing' => OrderStatus::PENDING,
            'processing' => OrderStatus::PROCESSING,
            'shipped' => OrderStatus::SHIPPED,
            'cancelled' => OrderStatus::CANCEL
        ];
        $userOrders = $user->orders()->with('product');

        if (array_key_exists($status, $statusMap)) {
            $userOrders = $userOrders->where('status', $statusMap[$status]);
        }

        if ($paginate) {
            return $userOrders
                ->orderBy('id', 'desc')
                ->paginate($paginate);
        }

        return $userOrders
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserTournamentsWithMatches(User $user)
    {
        $userTeams = $user->teams()->pluck('teams.id')->all();
        $participants = Participant::query()
            ->where(function (Builder $query) use ($user) {
                return $query->where('participantable_type', User::class)
                    ->where('participantable_id', $user->id);
            })
            ->orWhere(function (Builder $query) use ($userTeams) {
                return $query->where('participantable_type', Team::class)
                    ->whereIn('participantable_id', $userTeams);
            })
            ->get();
        $participantsIds = $participants->pluck('id')->all();
        $matches = Match::query()
            ->whereHas('plays', function (Builder $plays) use ($participantsIds) {
                return $plays->whereHas('parties', function (Builder $parties) use ($participantsIds) {
                    return $parties->whereIn('team_id', $participantsIds);
                });
            })
            ->orderBy('id', 'DESC')
            ->get();
        $matchesByTournamentId = [];
        foreach ($matches as $match) {
            $matchesByTournamentId[$match->tournament_id][] = $match;
        }
        $result = [];
        foreach ($matchesByTournamentId as $tournamentId => $tournamentMatches) {
            $tournament = Tournament::find($tournamentId);
            $engine = $tournament->engine();
            $tournamentArray = $tournament->toArray();
            $tournamentArray['matches'] = [];
            foreach ($tournamentMatches as $tournamentMatch) {
                $matchArray = $tournamentMatch->toArray();
                $matchArray['candidates'] = $tournamentMatch->getCandidates();
                $matchArray['round_title'] = $engine->getRoundTitle($tournamentMatch);
                $tournamentArray['matches'][] = $matchArray;
            }
            $result[] = $tournamentArray;
        }
        return $result;
    }
}
