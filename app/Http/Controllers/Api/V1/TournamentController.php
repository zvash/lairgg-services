<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HttpStatusCode;
use App\Invitation;
use App\Participant;
use App\Repositories\InvitationRepository;
use App\User;
use DateTimeZone;
use App\Tournament;
use App\Organization;
use Illuminate\Http\Request;
use App\Enums\TournamentStructure;
use Illuminate\Support\Facades\Auth;
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
     * Allow check in before check in time ha arrived
     *
     * @param Request $request
     * @param Tournament $tournament
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function allowCheckIn(Request $request, Tournament $tournament, TournamentRepository $tournamentRepository)
    {
        if (!$tournament) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('update', $tournament);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }
        $tournament = $tournamentRepository->allowCheckIn($tournament);
        return $this->success($tournament);
    }

    /**
     * Retrieves all participants of the given tournament
     *
     * @param Request $request
     * @param Tournament $tournament
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function participants(Request $request, Tournament $tournament)
    {
        if (!$tournament) {
            return $this->failNotFound();
        }
        return $this->success($tournament->participants->load(['participantable', 'participantable.players']));
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
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function featured(Request $request, TournamentRepository $tournamentRepository)
    {
        $tournaments = $tournamentRepository->getAllFeatured(10);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function live(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $tournaments = $tournamentRepository->getLive($user, 10);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function laterToday(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $tournaments = $tournamentRepository->getLaterTodayTournaments($user);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tomorrow(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $tournaments = $tournamentRepository->getTomorrowTournaments($user);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function willStartAfterTomorrow(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $tournaments = $tournamentRepository->getAfterTomorrowTournaments($user);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function willStartAfterNow(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $tournaments = $tournamentRepository->getAfterNowTournaments($user);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @param string $participantable
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function joinParticipantablesToTournament(Request $request, Tournament $tournament, string $participantable)
    {
        try {
            $participantable = $this->getParticipantablesType($participantable);
        } catch (\Exception $exception) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('canAddParticipants', $tournament);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        $this->validateJoinParticipantsRequest($request, $participantable);
        $participantsIds = $this->addNewParticipantsToTournament($request, $tournament, $participantable);
        return $this->success($participantsIds);
    }

    /**
     * @param Tournament $tournament
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function rules(Tournament $tournament)
    {
        return $this->success(['rules' => $tournament->rules]);
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function invite(Request $request, Tournament $tournament, InvitationRepository $invitationRepository)
    {
        $gate = Gate::inspect('canInviteParticipant', $tournament);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        list($identifier, $user) = $this->validateParticipantIdentifier($request);
        $invitationRepository->createTournamentInvitation($tournament, $identifier, Auth::user(), $user);
        return $this->success(['message' => "{$identifier} is invited to join the {$tournament->title} tournament."]);
    }


    /**
     * @param Request $request
     * @return array
     */
    private function validateParticipantIdentifier(Request $request)
    {
        $request->validate(['identifier' => 'required|string|filled']);
        $identifier = $request->get('identifier');
        $atSignPosition = strpos($identifier, '@');
        if ($atSignPosition === false) {
            $request->validate(['identifier' => 'exists:users,username']);
            $user = User::where('username', $identifier)->first();
            return [$identifier, $user];
        }
        $request->validate(['identifier' => 'email:rfc,dns']);
        $user = User::where('email', $identifier)->first();
        return [$identifier, $user];
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

    /**
     * @param Request $request
     * @param string $participantable
     */
    private function validateJoinParticipantsRequest(Request $request, string $participantable): void
    {
        $rules = [
            'participants' => 'required|array|min:1'
        ];
        if ($participantable == \App\User::class) {
            $rules[] = [
                'participants.*' => 'required|int|exists:users,id',
            ];
        } else if ($participantable == \App\Team::class) {
            $rules[] = [
                'participants.*' => 'required|int|exists:teams,id',
            ];
        }
        $request->validate($rules);
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @param string $participantable
     * @return array|mixed
     */
    private function addNewParticipantsToTournament(Request $request, Tournament $tournament, string $participantable)
    {
        $participantsIds = $request->get('participants');
        $joinedParticipantsIds = $tournament->participants->pluck('participantable_id')->toArray();
        $participantsIds = array_filter($participantsIds, function ($item) use ($joinedParticipantsIds) {
            return !in_array($item, $joinedParticipantsIds);
        });
        if ($participantsIds) {
            $newParticipants = [];
            foreach ($participantsIds as $id) {
                $newParticipants[] = new Participant([
                    'participantable_type' => $participantable,
                    'participantable_id' => $id
                ]);
            }
            $tournament->participants()->saveMany($newParticipants);
        }
        return $participantsIds;
    }

    /**
     * @param string $participantable
     * @return mixed|string
     * @throws \Exception
     */
    private function getParticipantablesType(string $participantable)
    {
        $participantables = [
            'users' => \App\User::class,
            'teams' => \App\Team::class,
        ];
        if (array_key_exists(strtolower($participantable), $participantables)) {
            $participantable = $participantables[$participantable];
        } else {
            throw new \Exception('Not Found');
        }
        return $participantable;
    }
}
