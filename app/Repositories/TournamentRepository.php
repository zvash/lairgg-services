<?php

namespace App\Repositories;


use App\Engines\BracketCreator;
use App\Enums\ParticipantAcceptanceState;
use App\Events\ParticipantStatusWasUpdated;
use App\Http\Requests\JoinTournamentRequest;
use App\Http\Requests\TournamentJoinRequest;
use App\Http\Requests\UpdateParticipantStatus;
use App\Match;
use App\Organization;
use App\Participant;
use App\Party;
use App\Team;
use App\TeamBalance;
use App\Tournament;
use App\TournamentType;
use App\User;
use App\UserBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


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
            //'matches',
            //'matches.plays',
            //'prizes',
            'links',
            'links.linkType'
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
        $userCheckedIn = false;

        $teamParticipants = $tournament->players > 1;
        if ($teamParticipants) {
            $currentUserTeams = $user->teams->pluck('id')->all();
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
            $userCheckedIn = $participationRecord->checked_in_at != null;
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
        //$tournament['participants'] = $participants;
        $tournament['current_size'] = count($participants);
        $tournament['join_status'] = [
            'can_join' => $userCanJoin,
            'status' => $userJoinStatus,
            'checked_in' => $userCheckedIn,
            'description' => Participant::getAcceptanceStatusDescription($userJoinStatus),
        ];
        return $tournament;
    }

    /**
     * @param Tournament $tournament
     * @return array
     */
    public function getPrizes(Tournament $tournament)
    {
        $engine = $tournament->engine();
        $maxRank = $this->findMaxRank($tournament);
        $standing = [];
        for ($i = 1; $i <= $maxRank; $i++) {
            $prizes = $tournament->prizes()
                ->where('rank', $i)
                ->with('valueType')
                ->get()
                ->all();
            if ($prizes) {
                $standing[] = [
                    'rank' => $i,
                    'prizes' => $prizes,
                    'winner' => $engine->getParticipantByRank($i)
                ];
            }

        }
        return $standing;
    }

    /**
     * @param Tournament $tournament
     * @return float|int
     */
    private function findMaxRank(Tournament $tournament)
    {
        $tournamentType = TournamentType::where('id', $tournament->tournament_type_id)->first();
        if ($tournamentType->title == 'Single Elimination') {
            if ($tournament->match_third_rank) {
                return 4;
            } else {
                return 2;
            }
        }
        if ($tournamentType->title == 'Double Elimination') {
//            return $tournament->matches()
//                ->where('group', 1)
//                ->where('round', 1)
//                ->count() * 2;
            return 4;
        }

        return $tournament->participants()->count();
    }

    /**
     * @param Tournament $tournament
     * @param string $bracket
     * @return array
     */
    public function rounds(Tournament $tournament, string $bracket)
    {
        $maxRounds = ceil(log($tournament->max_teams, 2));
        $isDoubleElimination = false;
        $isSingleElimination = false;
        $tournamentType = TournamentType::where('id', $tournament->tournament_type_id)->first();
        if ($tournamentType->title == 'Double Elimination') {
            $isDoubleElimination = true;
        }
        if ($tournamentType->title == 'Single Elimination') {
            $isSingleElimination = true;
        }
        $rounds = [];
        if ($bracket == 'winners') {
            for ($i = 1; $i <= $maxRounds; $i++) {
                if ($i == $maxRounds) {
                    $rounds[] = [
                        'group' => 1,
                        'round' => $i,
                        'name' => 'Final',
                    ];
                } else if ($i == $maxRounds - 1) {
                    $rounds[] = [
                        'group' => 1,
                        'round' => $i,
                        'name' => 'Semifinals',
                    ];
                } else if ($i == $maxRounds - 2) {
                    $rounds[] = [
                        'group' => 1,
                        'round' => $i,
                        'name' => 'Quarterfinals',
                    ];
                } else {
                    $playersCount = pow(2, $maxRounds + 1 - $i);
                    $rounds[] = [
                        'group' => 1,
                        'round' => $i,
                        'name' => 'Round of ' . $playersCount,
                    ];
                }
            }
            if ($isDoubleElimination) {
                $rounds[] = [
                    'group' => 3,
                    'round' => 1,
                    'name' => 'Grand Final',
                ];
            } else if ($isSingleElimination) {
                $rounds[] = [
                    'group' => 2,
                    'round' => 1,
                    'name' => 'Third Rank',
                ];
            }
        } else if ($isDoubleElimination) {
            $losersMaxRounds = ($maxRounds - 1) * 2;
            for ($i = 1; $i <= $losersMaxRounds; $i++) {
                if ($i == $losersMaxRounds) {
                    $rounds[] = [
                        'group' => 2,
                        'round' => $i,
                        'name' => 'Loser\'s Final',
                    ];
                } else {
                    $rounds[] = [
                        'group' => 2,
                        'round' => $i,
                        'name' => 'Loser\'s Round ' . $i,
                    ];
                }
            }
        }
        return $rounds;
    }

    private function roundsTitleByGroupAndRound(Tournament $tournament, string $bracket)
    {
        $rounds = $this->rounds($tournament, $bracket);
        $roundsMap = [];
        foreach ($rounds as $round) {
            $roundsMap[$round['group']][$round['round']] = $round['name'];
        }
        return $roundsMap;
    }

    /**
     * @param Tournament $tournament
     * @param string $bracket
     * @return array
     */
    public function bracketMatches(Tournament $tournament, string $bracket)
    {
        $winnerGroup = [1];
        $loserGroup = [0];
        $tournamentType = TournamentType::where('id', $tournament->tournament_type_id)->first();
        if ($tournamentType->title == 'Single Elimination') {
            $winnerGroup = [1, 2];
        } else if ($tournamentType->title == 'Double Elimination') {
            $loserGroup = [2];
            $winnerGroup = [1, 3];
        }
        $rounds = $this->roundsTitleByGroupAndRound($tournament, $bracket);
        $bracketMatches = [];
        if ($bracket == 'winners') {
            $bracketGroup = $winnerGroup;
        } else {
            $bracketGroup = $loserGroup;
        }
        $matches = $tournament->matches()
            ->whereIn('group', $bracketGroup)
            ->orderBy('id')
            ->get();

        foreach ($matches as $match) {
            $bracketMatch = $match->toArray();
            $bracketMatch['candidates'] = $match->getCandidates();
            $bracketMatches[$rounds[$match->group][$match->round]][] = $bracketMatch;
        }
        $allMatches = [];
        foreach ($bracketMatches as $title => $roundMatches) {
            $allMatches[] = [
                'title' => $title,
                'matches' => $roundMatches,
            ];
        }
        return $allMatches;
    }

    /**
     * @param Tournament $tournament
     * @param string $bracket
     * @param int $round
     * @return array
     */
    public function matches(Tournament $tournament, string $bracket, int $round)
    {
        $winnerGroup = 1;
        $loserGroup = 0;
        $thirdRankGroup = 0;
        $tournamentType = TournamentType::where('id', $tournament->tournament_type_id)->first();
        if ($tournamentType->title == 'Single Elimination') {
            $thirdRankGroup = 2;
        } else if ($tournamentType->title == 'Double Elimination') {
            $loserGroup = 2;
            $thirdRankGroup = 3;
        }
        if ($bracket == 'winners') {
            $matches = $tournament->matches()
                ->where(function ($query) use ($winnerGroup, $round) {
                    return $query->where('group', $winnerGroup)
                        ->where('round', $round);
                });

            if ($round == ceil(log($tournament->max_teams, 2))) {
                $matches = $matches->orWhere(function ($query) use ($thirdRankGroup, $tournament) {
                    return $query->where('tournament_id', $tournament->id)
                        ->where('group', $thirdRankGroup)
                        ->where('round', 1);
                });
            }
        } else if ($bracket == 'losers') {
            $matches = $tournament->matches()
                ->where('group', $loserGroup)
                ->where('round', $round);
        }
        $matches = $matches->get();
        $list = [];
        foreach ($matches as $match) {
            $matchData = $match->toArray();
            $participants = $match->getParticipants();
            $participantsScoreById = [];
            foreach ($participants as $participant) {
                $participantsScoreById[$participant->id] = $match->getParticipantScore($participant);
            }
            $participants = $participants->toArray();
            foreach ($participants as $index => $participant) {
                $participants[$index]['score'] = $participantsScoreById[$participant['id']];
            }
            $matchData['participants'] = $participants;
            $list[] = $matchData;
        }
        return $list;
    }

    /**
     * @param Tournament $tournament
     * @param int $participantableId
     * @return array
     */
    public function getParticipant(Tournament $tournament, int $participantableId)
    {
        $type = $tournament->players > 1 ? Team::class : User::class;
        $participant = $tournament->participants()
            ->where('participantable_type', $type)
            ->where('participantable_id', $participantableId)
            ->with('participantable')
            ->first();

        $players = [];
        $matches = [];
        if ($participant) {
            if ($type == Team::class) {
                $tournamentPlayers = $participant->participantable->players;
                foreach ($tournamentPlayers as $tournamentPlayer) {
                    $players[] = [
                        'user_id' => $tournamentPlayer->user_id,
                        'username' => $tournamentPlayer->username,
                        'avatar' => $tournamentPlayer->avatar,
                    ];
                }
            }
            $tournamentMatches = Match::query()
                ->where('tournament_id', $tournament->id)
                ->whereHas('plays', function ($plays) use ($participant) {
                    return $plays->whereHas('parties', function ($parties) use ($participant) {
                        return $parties->where('team_id', $participant->id);
                    });
                })
                ->orderBy('id', 'desc')
                ->get();
            foreach ($tournamentMatches as $tournamentMatch) {
                $match = $tournamentMatch->toArray();
                $match['candidates'] = $tournamentMatch->getCandidates();
                $matches[] = $match;
            }
        }
        return [
            'players' => $players,
            'matches' => $matches
        ];
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return |null
     */
    public function getUserMatchesInTournament(User $user, Tournament $tournament)
    {
        $participant = $this->getUserParticipantInTournament($user, $tournament);
        if (!$participant) {
            return null;
        }
        $matches = $tournament->matches()
            ->whereHas('plays', function (Builder $plays) use ($participant) {
                return $plays->whereHas('parties', function (Builder $parties) use ($participant) {
                    return $parties->where('team_id', $participant->id);
                });
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|Match
     */
    public function getCurrentMatchInTournament(User $user, Tournament $tournament)
    {

        $participant = $this->getUserParticipantInTournament($user, $tournament);
        if (!$participant) {
            return null;
        }
        $match = $tournament->matches()
            ->whereNull('winner_team_id')
            ->whereHas('plays', function (Builder $plays) use ($participant) {
                return $plays->whereHas('parties', function (Builder $parties) use ($participant) {
                    return $parties->where('team_id', $participant->id);
                });
            })
            ->first();
        return $match;
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return array
     */
    public function getUserTeamsForTournament(User $user, Tournament $tournament)
    {
        $teams = $user->teams()->with('players')->get();
        $playerCount = $tournament->players;
        $availableTeams = [];
        $unavailableTeams = [];
        $informCaptainTeams = [];
        foreach ($teams as $team) {
            if (
                $team->pivot->captain
                && $team->players()->count() >= $playerCount
            ) {
                $availableTeams[] = $team->toArray();
            } else if (
                !$team->pivot->captain
                && $team->players()->count() >= $playerCount
            ) {
                $informCaptainTeams[] = $team->toArray();
            } else {
                $unavailableTeams[] = $team->toArray();
            }
        }
        return [
            'available_teams' => $availableTeams,
            'unavailable_teams' => $unavailableTeams,
            'inform_captain_teams' => $informCaptainTeams,
        ];
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
     * Tournaments that will start tomorrow
     *
     * @param User $user
     * @param int $paginate
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUpcomingTournaments(User $user, int $paginate = 0)
    {
        $tournaments = $this->withGames(Tournament::upcoming(), $user);
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

    /**
     * @param Tournament $tournament
     * @return array|null
     */
    public function createBracket(Tournament $tournament)
    {
        $bracketCreator = new BracketCreator($tournament);
        return $bracketCreator->createBracket();
    }

    /**
     * @param Tournament $tournament
     * @param TournamentJoinRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function registerJoinRequest(Tournament $tournament, TournamentJoinRequest $request)
    {
        $user = $request->user();
        $inputs = $request->validated();
        $participantableId = $inputs['participantable_id'];
        $teamTournament = $tournament->players > 1;

        if ($teamTournament) {
            $team = $user->teams()->where('teams.id', $participantableId)->first();
            if (!$team) {
                //throw error
            }
            $participantable = $team;
            $participantableType = Team::class;
        } else {
            if ($participantableId != $user->id) {
                //throw error
            }
            $participantable = $user;
            $participantableType = User::class;
        }
        $participant = Participant::query()
            ->where('tournament_id', $tournament->id)
            ->where('participantable_type', $participantableType)
            ->where('participantable_id', $participantable->id)
            ->first();

        if ($participant) {
            return $participant;
        }
        $participant = Participant::query()->create([
            'tournament_id' => $tournament->id,
            'participantable_type' => $participantableType,
            'participantable_id' => $participantable->id,
        ]);
        return $participant->refresh();
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return bool
     * @throws \Exception
     */
    public function removeUserFromTournament(User $user, Tournament $tournament)
    {
        //get participant
        $participant = $this->getuserparticipantintournament($user, $tournament);
        if (!$participant) {
            throw new \Exception('User is not a participant of this tournament');
        }
        //check if user has privilege
        if (!$this->userIsInChargeOfParticipant($user, $participant)) {
            throw new \Exception('User has not enough privilege to decide for the tournament participant');
        }
        //check if tournament is started
        if ($this->tournamentIsStarted($tournament)) {
            throw new \Exception('User cannot leave an already started tournament');
        }
        //remove participant from bracket
        Party::query()
            ->where('team_id', $participant->id)
            ->update(['team_id' => null]);
        $participant->delete();
        return true;
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return Participant|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     * @throws \Exception
     */
    public function checkUserInTournament(User $user, Tournament $tournament)
    {
        //get participant
        $participant = $this->getuserparticipantintournament($user, $tournament);
        if (!$participant) {
            throw new \Exception('User is not a participant of this tournament');
        }

        if (!in_array($participant->status, [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])) {
            throw new \Exception('You are not accepted yet');
        }

        //check if user has privilege
        if (!$this->userIsInChargeOfParticipant($user, $participant)) {
            throw new \Exception('User has not enough privilege to decide for the tournament participant');
        }
        //check if tournament is started
        if ($this->checkinIsNotAllowed($tournament)) {
            throw new \Exception('User cannot check in an already started tournament');
        }
        $participant->checked_in_at = Carbon::now();
        $participant->save();
        //$tournament->engine()->assignParticipantToFirstEmptyMatch($participant);
        return $participant;
    }

    /**
     * @param UpdateParticipantStatus $request
     * @param Tournament $tournament
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     * @throws \Exception
     */
    public function updateParticipantStatus(UpdateParticipantStatus $request, Tournament $tournament, string $status)
    {
        $inputs = $request->validated();
        $participantId = $inputs['participant_id'];
        $participant = $tournament->participants()->where('participants.id', $participantId)->first();
        if ($participant) {
            if ($participant->status != $status) {
                $participant->status = $status;
                $participant->save();
                event(new ParticipantStatusWasUpdated($participant));
            }
            return $participant;
        }
        throw new \Exception('This operation is not performable');
    }

    /**
     * @param Tournament $tournament
     * @return string
     */
    public function releaseGems(Tournament $tournament)
    {
        if (!$tournament->hasFinished()) {
            return 'Tournament has not finished yet.';
        }
        $participantsByRank = $tournament->getRankedParticipants();
        $gemsByRank = $tournament->getGemPrizesByRank();
        foreach ($gemsByRank as $rank => $points) {
            if (isset($participantsByRank[$rank])) {
                $participant = $participantsByRank[$rank];
                try {
                    if ($participant->participantable_type == Team::class) {
                        TeamBalance::query()
                            ->create([
                                'tournament_id' => $tournament->id,
                                'team_id' => $participant->participantable_id,
                                'points' => $points,
                            ]);
                    } else if ($participant->participantable_type == User::class) {
                        UserBalance::query()
                            ->create([
                                'tournament_id' => $tournament->id,
                                'user_id' => $participant->participantable_id,
                                'points' => $points,
                            ]);
                        User::find($participant->participantable_id)->points($points);
                    }
                } catch (\Exception $exception) {
                    return 'Already Released';
                }
            }
        }
        return 'done';
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
                'requires_score',
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
            $participants->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])
                ->whereHasMorph('participantable', [User::class, Team::class], function (Builder $participantable, $type) use ($user) {
                    if ($type == Team::class) {
                        $participantable->whereHas('players', function (Builder $players) use ($user) {
                            $players->where('user_id', $user->id);
                        });
                    } else {
                        $participantable->where('id', $user->id);
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

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null|Participant
     */
    private function getUserParticipantInTournament(User $user, Tournament $tournament)
    {
        $teamTournament = $tournament->players > 1;
        if ($teamTournament) {
            $userTeams = $user->teams()->pluck('teams.id')->all();
            $userTeams[] = 0;
            $participant = $tournament
                ->participants()
                ->where('participantable_type', Team::class)
                ->whereIn('participantable_id', $userTeams)
                ->first();
        } else {
            $participant = $tournament->participants()
                ->where('participantable_type', User::class)
                ->where('participantable_id', $user->id)
                ->first();
        }
        return $participant;
    }

    /**
     * @param User $user
     * @param Participant $participant
     * @return bool
     */
    private function userIsInChargeOfParticipant(User $user, Participant $participant)
    {
        if ($participant->participantable_type == User::class) {
            return $participant->participantable_id == $user->id;
        }
        if ($participant->participantable_type == Team::class) {
            $captain = $participant->participantable->players()->where('captain', 1)->first();
            return $captain && $captain->user_id == $user->id;
        }
        return false;
    }

    /**
     * @param Tournament $tournament
     * @return bool
     */
    private function tournamentIsStarted(Tournament $tournament)
    {
        return $tournament->started_at < \Carbon\Carbon::now() ||
            $tournament->matches()->whereNotNull('winner_team_id')->count() > 0;

    }

    /**
     * @param Tournament $tournament
     * @return bool
     */
    private function checkinIsNotAllowed(Tournament $tournament)
    {
        return !$tournament->allow_check_in && (
                $tournament->started_at < \Carbon\Carbon::now() ||
                $tournament->matches()->whereNotNull('winner_team_id')->count() > 0
            );
    }
}
