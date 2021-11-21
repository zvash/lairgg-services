<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Http\Requests\UpdateUserRequest;
use App\Match;
use App\Participant;
use App\Prize;
use App\Team;
use App\Tournament;
use App\TournamentAnnouncement;
use App\User;
use App\UserLastTournamentAnnouncement;
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
     * @param UpdateUserRequest $request
     * @return User
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['ip'] = $request->ip();
        $path = $this->saveImageFromRequest($request, 'avatar', 'users/avatars');
        if ($path) {
            $validated['avatar'] = $path;
        }
        $path = $this->saveImageFromRequest($request, 'cover', 'users/covers');
        if ($path) {
            $validated['cover'] = $path;
        }
        User::query()
            ->where('id', $user->id)
            ->update($validated);
        return User::find($user->id);
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
        $participantsIdsByTournamentIds = $participants->pluck('id', 'tournament_id')->all();
        $participantsIds = array_values($participantsIdsByTournamentIds);
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
            $rank = null;
            $needRanking = true;
            foreach ($tournamentMatches as $tournamentMatch) {
                if ($needRanking) {
                    $needRanking = false;
                    if ($tournamentMatch && $tournamentMatch->winner_team_id) {
                        if ($tournamentMatch->winner_team_id == $participantsIdsByTournamentIds[$tournamentMatch->tournament_id]) {
                            $rank = $tournamentMatch->getWinnerRank();
                        } else {
                            $rank = $tournamentMatch->getLoserRank();
                        }
                    }
                }
                $tournamentArray['rank'] = $rank;
                $matchArray = $tournamentMatch->toArray();
                $matchArray['candidates'] = $tournamentMatch->getCandidates();
                $matchArray['round_title'] = $engine->getRoundTitle($tournamentMatch);
                $tournamentArray['matches'][] = $matchArray;
            }
            $result[] = $tournamentArray;
        }
        return $result;
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getTournamentAnnouncements(User $user, Tournament $tournament)
    {
        $announcements = TournamentAnnouncement::query()
            ->where('tournament_id', $tournament->id)
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->makeHidden('staff')
            ->toArray();

        $lastAnnouncement = TournamentAnnouncement::query()
            ->where('tournament_id', $tournament->id)
            ->orderBy('id', 'desc')
            ->first();
        if ($lastAnnouncement) {
            UserLastTournamentAnnouncement::query()
                ->updateOrCreate(
                    ['user_id' => $user->id, 'tournament_id' => $tournament->id],
                    ['tournament_announcement_id' => $lastAnnouncement->id]
                );
        }

        return $announcements;
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return int
     */
    public function getTournamentAnnouncementsUnreadCount(User $user, Tournament $tournament)
    {
        $lastId = 0;
        $lastAnnouncement = $user->lastTournamentAnnouncement()
            ->where('tournament_id', $tournament->id)
            ->first();
        if ($lastAnnouncement) {
            $lastId = $lastAnnouncement->tournament_announcement_id;
        }
        return TournamentAnnouncement::query()
            ->where('tournament_id', $tournament->id)
            ->where('id', '>', $lastId)
            ->count();
    }

    public function info(User $user)
    {
        $userData = array_filter($user->toArray(), function ($key) {
            return in_array($key, [
                'id',
                'first_name',
                'last_name',
                'username',
                'avatar',
                'cover',
                'bio',
                'country_detail',
            ]);
        }, ARRAY_FILTER_USE_KEY);

        $participantsIdsByTournamentIds = Participant::query()
            ->where('participantable_type', User::class)
            ->where('participantable_id', $user->id)
            ->get()
            ->pluck('id', 'tournament_id')
            ->all();

        $tournamentIds = array_keys($participantsIdsByTournamentIds);
        $participantIds = array_values($participantsIdsByTournamentIds);

        $matchesIdsByTournamentIds = Match::selectRaw('MAX(id) as id, tournament_id')
            ->whereIn('tournament_id', $tournamentIds)
            ->whereHas('plays', function ($plays) use ($participantIds) {
                return $plays->whereHas('parties', function ($parties) use ($participantIds) {
                    return $parties->whereIn('team_id', $participantIds);
                });
            })->groupByRaw('tournament_id')->get()
            ->pluck('id', 'tournament_id')->all();
        $ranksCount = [
            1 => 0,
            2 => 0,
            3 => 0,
        ];
        foreach ($matchesIdsByTournamentIds as $matchId) {
            $match = Match::find($matchId);
            if ($match && $match->winner_team_id) {
                if ($match->winner_team_id == $participantsIdsByTournamentIds[$match->tournament_id]) {
                    $rank = $match->getWinnerRank();
                } else {
                    $rank = $match->getLoserRank();
                }
                if ($rank && in_array($rank, [1, 2, 3])) {
                    $ranksCount[$rank] += 1;
                }
            }
        }
        $userData['ranks'] = $ranksCount;
        return $userData;
    }

    /**
     * @param User $user
     * @return array
     */
    public function about(User $user)
    {
        $userData = array_filter($user->toArray(), function ($key) {
            return in_array($key, [
                'id',
                'bio',
            ]);
        }, ARRAY_FILTER_USE_KEY);
        $games = $user->games->toArray();
        $links = $user->links->load('linkType')->toArray();
        $userData['games'] = $games;
        $userData['links'] = $links;
        return $userData;
    }

    /**
     * @param User $user
     * @return array
     */
    public function awards(User $user)
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
        $participantsIdsByTournamentIds = $participants->pluck('id', 'tournament_id')->all();
        $participantsIds = array_values($participantsIdsByTournamentIds);
        $tournamentIds = array_keys($participantsIdsByTournamentIds);
        $matchesIdsByTournamentIds = \App\Match::selectRaw('MAX(id) as id, tournament_id')
            ->whereIn('tournament_id', $tournamentIds)
            ->whereHas('plays', function ($plays) use ($participantsIds) {
                return $plays->whereHas('parties', function ($parties) use ($participantsIds) {
                    return $parties->whereIn('team_id', $participantsIds);
                });
            })->groupByRaw('tournament_id')->orderBy('id', 'desc')->get()
            ->pluck('id', 'tournament_id')->all();
        $prizes = [];
        foreach ($matchesIdsByTournamentIds as $matchId) {
            $rank = null;
            $match = Match::find($matchId);
            if ($match && $match->winner_team_id) {
                if ($match->winner_team_id == $participantsIdsByTournamentIds[$match->tournament_id]) {
                    $rank = $match->getWinnerRank();
                } else {
                    $rank = $match->getLoserRank();
                }
                if ($rank) {
                    $item = Tournament::where('id', $match->tournament_id)
                        ->with(['game', 'organization'])->first()->toArray();
                    $item['rank'] = $rank;
                    $item['prizes'] = Prize::query()->where('tournament_id', $match->tournament_id)
                        ->where('rank', $rank)
                        ->get()->toArray();
                    $prizes[] = $item;

                }
            }
        }
        return $prizes;
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function teams(User $user)
    {
        return $user->teams->load(['players']);
    }
}
