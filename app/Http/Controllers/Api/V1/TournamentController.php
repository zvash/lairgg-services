<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HttpStatusCode;
use DateTimeZone;
use App\Tournament;
use App\Organization;
use Illuminate\Http\Request;
use App\Enums\TournamentStructure;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Traits\Responses\ResponseMaker;
use App\Repositories\TournamentRepository;
use App\Traits\Responses\ValidityChecker;

class TournamentController extends Controller
{
    use ResponseMaker;
    use ValidityChecker;

    /**
     * Creates a new tournament
     *
     * @param Request $request
     * @param $
     * @param int $organizationId
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request, int $organizationId, TournamentRepository $tournamentRepository)
    {
        $organization = Organization::find($organizationId);
        if (!$organization) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('createByOrganization', [Tournament::class, $organization]);
        if ($gate->denied()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        list($failed, $validator) = $this->validateCreateTournament($request);
        if ($failed) {
            return $this->failValidation($validator->errors());
        }

        $tournament = $tournamentRepository->createTournamentWithRequest($request, $organization);
        return $this->success($tournament);
    }

    /**
     * Edit the given tournament
     *
     * @param Request $request
     * @param int $tournamentId
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function edit(Request $request, int $tournamentId, TournamentRepository $tournamentRepository)
    {
        $tournament = Tournament::find($tournamentId);
        if (!$tournament) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('update', $tournament);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        list($failed, $validator) = $this->validateEditTournament($request);
        if ($failed) {
            return $this->failValidation($validator->errors());
        }

        $tournament = $tournamentRepository->editTournamentWithRequest($request, $tournament);
        return $this->success($tournament);
    }

    /**
     * @param Tournament $tournament
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function overview(Tournament $tournament)
    {
        if ($tournament) {
            $tournament->load([
                'game',
                'region',
                'participants',
                'prizes',
                'matches',
                'matches.plays',
                'game.gameType'
            ]);
            return $this->success($tournament);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateCreateTournament(Request $request)
    {
        $timezones = collect(DateTimeZone::listIdentifiers(DateTimeZone::ALL))->map(function ($timezone) {
            return $timezone;
        })->all();
        $structures = [
            TournamentStructure::SIX,
            TournamentStructure::FIVE,
            TournamentStructure::FOUR,
            TournamentStructure::THREE,
            TournamentStructure::TWO,
            TournamentStructure::ONE,
            TournamentStructure::OTHER,
        ];
        $rules = [
            'title' => 'required|filled',
            'description' => 'string',
            'rules' => 'string',
            'image' => 'required|mimes:jpeg,jpg,png',
            'cover' => 'mimes:jpeg,jpg,png',
            'timezone' => 'required|string|in:' . implode(',', $timezones),
            'max_teams' => 'required|int|min:1|max:128',
            'reserve_teams' => 'required|int|min:0',
            'players' => 'required|int|min:1',
            'check_in_period' => 'required|int|min:1',
            'entry_fee' => 'required|numeric|min:0',
            'listed' => 'boolean',
            'join_request' => 'boolean',
            'join_url' => 'url',
            'status' => 'boolean',
            'structure' => 'required|in:' . implode(',', $structures),
            'match_check_in_period' => 'int|min:0',
            'match_play_count' => 'int|min:0',
            'match_randomize_map' => 'boolean',
            'match_third_rank' => 'boolean',
            'league_win_score' => 'int|min:0',
            'league_tie_score' => 'int|min:0',
            'league_lose_score' => 'int|min:0',
            'league_match_up_count' => 'int|min:0',
            'region_id' => 'required|int|exists:regions,id',
            'tournament_type_id' => 'required|int|exists:tournament_types,id',
            'game_id' => 'required|int|exists:games,id'
        ];
        return $this->validateRules($request, $rules);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateEditTournament(Request $request)
    {
        $timezones = collect(DateTimeZone::listIdentifiers(DateTimeZone::ALL))->map(function ($timezone) {
            return $timezone;
        })->all();
        $structures = [
            TournamentStructure::SIX,
            TournamentStructure::FIVE,
            TournamentStructure::FOUR,
            TournamentStructure::THREE,
            TournamentStructure::TWO,
            TournamentStructure::ONE,
            TournamentStructure::OTHER,
        ];
        $rules = [
            'title' => 'filled',
            'description' => 'string',
            'rules' => 'string',
            'image' => 'mimes:jpeg,jpg,png',
            'cover' => 'mimes:jpeg,jpg,png',
            'timezone' => 'string|in:' . implode(',', $timezones),
            'max_teams' => 'int|min:1|max:128',
            'reserve_teams' => 'int|min:0',
            'players' => 'int|min:1',
            'check_in_period' => 'int|min:1',
            'entry_fee' => 'numeric|min:0',
            'listed' => 'boolean',
            'join_request' => 'boolean',
            'join_url' => 'url',
            'status' => 'boolean',
            'structure' => 'in:' . implode(',', $structures),
            'match_check_in_period' => 'int|min:0',
            'match_play_count' => 'int|min:0',
            'match_randomize_map' => 'boolean',
            'match_third_rank' => 'boolean',
            'league_win_score' => 'int|min:0',
            'league_tie_score' => 'int|min:0',
            'league_lose_score' => 'int|min:0',
            'league_match_up_count' => 'int|min:0',
            'region_id' => 'int|exists:regions,id',
            'tournament_type_id' => 'int|exists:tournament_types,id',
            'game_id' => 'int|exists:games,id'
        ];
        return $this->validateRules($request, $rules);
    }
}
