<?php

namespace App\Repositories;


use App\Organization;
use App\Tournament;
use Illuminate\Http\Request;

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
        $inputs['organization_id'] = $organization->id;
        $inputs['image'] = $this->saveImageFromRequest($request, 'image', 'tournaments/images');
        $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'tournaments/covers');
        return Tournament::create($inputs);
    }
}