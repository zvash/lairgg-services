<?php

namespace App\Repositories;


use App\Organization;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;


class TournamentRepository extends BaseRepository
{
    protected $modelClass = Tournament::class;

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
     * @return mixed
     */
    public function createTournamentWithRequest(Request $request, Organization $organization)
    {
        $inputs = $this->filterRequest($request);
        $inputs['organization_id'] = $organization->id;
        $inputs['image'] = $this->saveImageFromRequest($request, 'image', 'tournaments/images');
        $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'tournaments/covers');
        return Tournament::create($inputs);
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
        $gameIds = $user->games()->pluck('game_id');
        return $query->whereHas('game', function($game) use ($gameIds) {
            return $game->whereIn('id', $gameIds);
        });
    }
}