<?php

namespace App\Repositories;


use App\Engines\BracketCreator;
use App\Enums\ParticipantAcceptanceState;
use App\Organization;
use App\Participant;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;


class TournamentRepository extends BaseRepository
{
    protected $modelClass = Tournament::class;

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return Tournament|array
     */
    public function getTournamentOverview(User $user, Tournament $tournament)
    {
        $tournament->load([
            'organization',
            'tournamentType',
            'matches',
            'matches.plays',
            'prizes',
            'links',
        ]);
        $totalPrize = $tournament->prizes()
            ->whereHas('valueType', function ($query) {
                return $query->where('title', 'Point');
            })
            ->sum('value');
        $participants = $tournament->participants()
            ->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])
            ->with('participantable')
            ->get()
            ->toArray();

        $userCanJoin = false;
        $userJoinStatus = 'not-joined';

        $teamParticipants = $tournament->players > 1;
        if ($teamParticipants) {
            $currentUserTeams = $user->teams()->pluck('id')->all();
            $participationRecord = Participant::query()
                ->where('tournament_id', $tournament->id)
                ->where('participantable_type', Team::class)
                ->whereIn('participantable_id', $currentUserTeams)
                ->first();
        } else {
            $participationRecord = Participant::query()
                ->where('tournament_id', $tournament->id)
                ->where('participantable_type', User::class)
                ->where('participantable_id', $user->id)
                ->first();
        }
        if ($participationRecord) {
            $userJoinStatus = $participationRecord->status;
        }
        $now = \Carbon\Carbon::now();
        $startTime = $tournament->started_at;
        $joinRequestDeadline = $startTime->subMinutes($tournament->check_in_period);
        if ($tournament->listed && $now < $joinRequestDeadline) {
            $userCanJoin = true;
        }
        $startsInMinutes = $tournament->started_at->diffInMinutes($now);
        $endsInMinutes = $tournament->ended_at->diffInMinutes($now);
        $tournament = $tournament->toArray();
        $tournament['total_prize'] = $totalPrize;
        $tournament['starts_in_minutes'] = $startsInMinutes;
        $tournament['ends_in_minutes'] = $endsInMinutes;
        $tournament['check_in_date'] = $joinRequestDeadline;
        $tournament['participants_type'] = $teamParticipants ? 'team' : 'player';
        $tournament['participants'] = $participants;
        $tournament['current_size'] = count($participants);
        $tournament['join_status'] = [
            'can_join' => $userCanJoin,
            'status' => $userJoinStatus,
        ];
        return $tournament;
    }

    /**
     * Retrieves a list of tournaments for given organization
     * and group them based on their started_at column
     * @param int $organizationId
     * @return array
     */
    public function tournamentsByDateForOrganization(int $organizationId)
    {
        $todayTournaments = Tournament::where('organization_id', $organizationId)
            ->today()->get()->toArray();
        $upcomingTournaments = Tournament::where('organization_id', $organizationId)
            ->upcoming()->get()->toArray();
        $lastMonthTournaments = Tournament::where('organization_id', $organizationId)
            ->lastMonth()->limit(2)->get()->toArray();

        $tournaments = $todayTournaments + $upcomingTournaments + $lastMonthTournaments;

        return $tournaments;
    }

    /**
     * Creates a new Tournament
     *
     * @param Request $request
     * @param Organization $organization
     * @param LobbyRepository $lobbyRepository
     * @return mixed
     */
    public function createTournamentWithRequest(Request $request, Organization $organization, LobbyRepository $lobbyRepository)
    {
        $inputs = $this->filterRequest($request);
        $inputs['organization_id'] = $organization->id;
        $inputs['image'] = $this->saveImageFromRequest($request, 'image', 'tournaments/images');
        $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'tournaments/covers');
        $tournament = Tournament::create($inputs);
        $lobbyRepository->createBy($tournament);
        return $tournament;
    }

    /**
     * Updates an existing tournament
     *
     * @param Request $request
     * @param Tournament $tournament
     * @return Tournament
     */
    public function editTournamentWithRequest(Request $request, Tournament $tournament)
    {
        $inputs = $this->filterRequest($request);
        $image = $this->saveImageFromRequest($request, 'image', 'tournaments/images');
        $cover = $this->saveImageFromRequest($request, 'cover', 'tournaments/covers');
        if ($image) {
            $inputs['image'] = $image;
        }
        if ($cover) {
            $inputs['cover'] = $cover;
        }
        foreach ($inputs as $key => $value) {
            $tournament->setAttribute($key, $value);
        }
        $tournament->save();
        return $tournament;
    }

    /**
     * @param Tournament $tournament
     * @return Tournament
     */
    public function allowCheckIn(Tournament $tournament)
    {
        $tournament->allow_check_in = true;
        $tournament->save();
        return $tournament;
    }

    /**
     * Get all featured tournaments
     *
     * @param int $paginate
     * @return mixed
     */
    public function getAllFeatured(int $paginate = 0)
    {
        $tournaments = Tournament::featured();
        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * Get all tournaments for a user
     *
     * @param User $user
     * @param int $paginate
     * @return mixed
     */
    public function getUserTournaments(User $user, int $paginate = 0)
    {
        $tournaments = $this->userTournamentsQueryBuilder($user);

        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * @param User $user
     * @param int $paginate
     * @return mixed
     */
    public function getUserUpcomingTournaments(User $user, int $paginate = 0)
    {
        $tournaments = $this->userTournamentsQueryBuilder($user)->upcomingMoment();

        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * @param User $user
     * @param int $limit
     * @return mixed
     */
    public function getUserFirstFewUpcomingTournaments(User $user, int $limit = 0)
    {
        $tournaments = $this->userTournamentsQueryBuilder($user)->upcomingMoment();

        if ($limit) {
            return $tournaments->limit($limit)->get()->toArray();
        }
        return $tournaments->limit(1)->get()->toArray();
    }

    /**
     * @param User $user
     * @param int $paginate
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function recentlyFinished(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::recentlyFinished(), $user);
        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * Get Live tournaments
     *
     * @param User $user
     * @param int $paginate
     * @return mixed
     */
    public function getLive(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::live(), $user);
        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * Get Live and Later Today tournaments
     *
     * @param User $user
     * @param int $paginate
     * @return mixed
     */
    public function getLivePlusLaterToday(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::todayOrLive(), $user);
        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * Tournaments that will start later today
     *
     * @param User $user
     * @param int $paginate
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLaterTodayTournaments(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::laterToday(), $user);
        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * Tournaments that will start tomorrow
     *
     * @param User $user
     * @param int $paginate
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getTomorrowTournaments(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::tomorrow(), $user);
        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * Tournaments that will start 2 days ahead
     *
     * @param User $user
     * @param int $paginate
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAfterTomorrowTournaments(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::startAfterTomorrow(), $user);
        if ($paginate) {
            return $tournaments->paginate($paginate, 10);
        }
        return $tournaments->get()->toArray();
    }

    /**
     * Tournaments that will start in future
     *
     * @param User $user
     * @param int $paginate
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAfterNowTournaments(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::upcomingMoment(), $user);
        if ($paginate) {
            return $tournaments->paginate($paginate);
        }
        return $tournaments->get()->toArray();
    }

    public function createBracket(Tournament $tournament)
    {
        $bracketCreator = new BracketCreator($tournament);
        $bracket = $bracketCreator->createBracket();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function filterRequest(Request $request): array
    {
        $inputs = array_filter($request->all(), function ($key) {
            return in_array($key, [
                'title',
                'description',
                'rules',
                'timezone',
                'min_teams',
                'max_teams',
                'reserve_teams',
                'players',
                'check_in_period',
                'entry_fee',
                'listed',
                'join_request',
                'join_url',
                'status',
                'structure',
                'match_check_in_period',
                'match_play_count',
                'match_randomize_map',
                'match_third_rank',
                'league_win_score',
                'league_tie_score',
                'league_lose_score',
                'league_match_up_count',
                'region_id',
                'tournament_type_id',
                'game_id',
            ]);
        }, ARRAY_FILTER_USE_KEY);
        return $inputs;
    }

    /**
     * @param User $user
     * @return mixed
     */
    protected function userTournamentsQueryBuilder(User $user)
    {
        $tournaments = Tournament::whereHas('participants', function (Builder $participants) use ($user) {
            $participants->whereHasMorph('participantable', [Team::class, User::class], function (Builder $participantable, $type) use ($user) {
                if ($type == Team::class) {
                    $participantable->whereHas('players', function (Builder $players) use ($user) {
                        $players->where('user_id', $user->id);
                    });
                } else {
                    $participantable->where('participantable_id', $user->id);
                }
            });
        });
        return $tournaments;
    }

    /**
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    private function withGames(Builder $query, User $user)
    {
        $request = request();
        if ($request->has('game_id')) {
            $gameIds = [$request->get('game_id')];
        } else {
            $gameIds = $user->games()->pluck('game_id');
        }
        return $query->whereHas('game', function ($game) use ($gameIds) {
            return $game->whereIn('id', $gameIds);
        });
    }
}