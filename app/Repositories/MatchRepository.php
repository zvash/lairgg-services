<?php

namespace App\Repositories;


use App\Dispute;
use App\Enums\ParticipantAcceptanceState;
use App\Jobs\FireUpPrematchPreparation;
use App\LobbyMessage;
use App\Map;
use App\Match;
use App\MatchParticipant;
use App\User;
use App\Organization;
use App\Participant;
use App\Party;
use App\Play;
use App\Team;
use App\Tournament;
use App\TournamentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MatchRepository extends BaseRepository
{
    protected $modelClass = Match::class;

    /**
     * Reset Number of plays in a match
     *
     * @param Match $match
     * @param int $count
     * @return Match
     */
    public function resetPlayCountForMatch(Match $match, int $count)
    {
        $currentCount = $match->plays()->count();
        if ($currentCount == $count) {
            return $match;
        }
        $match->play_count = $count;
        $match->save();
        $parties = null;
        $firstPlay = $match
            ->plays()
            ->first();
        if ($firstPlay) {
            $parties = $firstPlay
                ->parties
                ->pluck('team_id')
                ->all();
        }
        if ($currentCount > $count) {
            $plays = $match->plays()->orderBy('updated_at')->get()->all();
            $toDelete = $currentCount - $count;
            for ($i = 0; $i < $toDelete; $i++) {
                $plays[$i]->delete();
            }
            return $match;
        }
        $tournament = $match->tournament;
        $partiesCount = $this->getTournamentPartiesCount($tournament);
        $toCreate = $count - $currentCount;
        $playIndexes = range(1, $toCreate);
        foreach ($playIndexes as $index) {
            $play = Play::create([
                'match_id' => $match->id
            ]);
            for ($i = 0; $i < $partiesCount; $i++) {
                $teamId = null;
                if ($parties && isset($parties[$i])) {
                    $teamId = $parties[$i];
                }
                Party::create([
                    'play_id' => $play->id,
                    'team_id' => $teamId
                ]);
            }
        }
        return $match;
    }

    /**
     * @param Match $match
     * @return mixed
     */
    public function getDisputes(Match $match)
    {
        $playIds = $match
            ->plays()
            ->pluck('id')
            ->all();
        $playIds[] = 0;

        $disputes = Dispute::whereIn('play_id', $playIds)->get();
        return $disputes;
    }

    /**
     * @param Match $match
     * @return mixed|null
     */
    public function autoWinIfItIsARestMatch(Match $match)
    {
        if ($match->winner_team_id === null && $match->isRestMatch()) {
            $participant = $match->getParticipants()->first();
            $match->winner_team_id = $participant->id;
            $match->save();
            $match->addWinnerToNextMatchForWinners();
            return $match->id;
        }
        return null;
    }

    /**
     * @param Match $match
     * @param User $user
     * @return array
     */
    public function specificMatchOverview(Match $match, User $user)
    {
        $userIsCaptain = false;
        $tournament = $match->tournament;
        $organizer = $tournament->organization;
        $game = $tournament->game;
        $matchParticipants = $match->getParticipants();
        $plays = $match->plays()->orderBy('id')->with(['map', 'parties'])->get();
        $lobby = $match->lobby;
        $canSubmitScore = LobbyMessage::query()
            ->where('lobby_id', $lobby->id)
            ->where('type', 'guideline')
            ->count() > 0;
        $information = [
            'id' => $match->id,
            'match_date' => $match->started_at,
            'games_per_match' => $match->play_count,
            'stage' => $match->getRoundTitle(),
            'winner_participant_id' => $match->winner_team_id,
            'organizer' => [
                'id' => $organizer->id,
                'logo' => $organizer->logo,
                'cover' => $organizer->cover,
            ],
            'tournament' => [
                'id' => $tournament->id,
                'title' => $tournament->title,
                'image' => $tournament->image,
                'logo' => $tournament->logo,
                'players' => $tournament->players,
                'requires_score' => $tournament->requires_score,
                'bracket_released_at' => $tournament->bracket_released_at,
                'match_check_in_period' => $tournament->match_check_in_period,
            ],
            'game' => [
                'id' => $game->id,
                'title' => $game->title,
                'image' => $game->image,
                'cover' => $game->cover,
                'logo' => $game->logo,
            ],
            'maps' => $game->maps->toArray(),
            'ready_state' => $this->getReadyState($user, $match, $matchParticipants),
            'preparation_is_done' => $canSubmitScore,
        ];
        $participants = [];
        foreach ($matchParticipants as $participant) {
            $type = $participant->participantable_type == Team::class ? 'team' : 'user';
            $record = [
                'id' => $participant->id,
                'participantable_id' => $participant->participantable_id,
                'participantable_type' => $type,
                'score' => $match->getParticipantCurrentScore($participant),
                'is_winner' => $match->winner_team_id == $participant->id,
            ];

            if ($type == 'team') {
                $record['title'] = $participant->participantable->title;
                $record['image'] = $participant->participantable->logo;
                $participantPlayers = $participant->participantable->players;
                $players = [];
                foreach ($participantPlayers as $player) {
                    $playerUser = User::find($player->user_id);
                    $isCaptain = $player->captain == 1;
                    if ($isCaptain && $user->id == $playerUser->id) {
                        $userIsCaptain = true;
                    }
                    $players[] = [
                        'user_id' => $playerUser->id,
                        'username' => $playerUser->username,
                        'avatar' => $playerUser->avatar,
                        'is_captain' => $isCaptain,
                        'country_detail' => $playerUser->country_detail,
                    ];
                }
                $record['players'] = $players;
            } else if ($type == 'user') {
                $record['title'] = $participant->participantable->username;
                $record['image'] = $participant->participantable->avatar;
                $players = [];
                $playerUser = User::find($participant->participantable_id);
                $isCaptain = true;
                if ($participant->participantable_id == $user->id) {
                    $userIsCaptain = true;
                }
                $players[] = [
                    'user_id' => $playerUser->id,
                    'username' => $playerUser->username,
                    'avatar' => $playerUser->avatar,
                    'is_captain' => $isCaptain,
                    'country_detail' => $playerUser->country_detail,
                ];
                $record['players'] = $players;
            }
            $participants[] = $record;
        }
        $information['participants'] = $participants;
        $information['viewer_is_captain'] = $userIsCaptain;
        $information['plays'] = $plays->toArray();
        return $information;
    }

    /**
     * @param Match $match
     * @param User $user
     * @param LobbyRepository $lobbyRepository
     * @param PlayRepository $playRepository
     * @return mixed
     * @throws \Exception
     */
    public function setReady(Match $match, User $user, LobbyRepository $lobbyRepository, PlayRepository $playRepository)
    {
//        $playRepository->checkIfMatchScoresAreEditable($match);
        $participant = $this->findMatchParticipantByUser($match, $user);
        if (!$participant) {
            throw new \Exception(__('strings.match.you_are_not_participant'));
        }
        $now = Carbon::now();
        $startedAt = $match->started_at;
        $minutesAfterStartedAt = $startedAt->addMinutes($match->tournament->match_check_in_period);
//        if ($now->lt($match->started_at) || $now->gt($minutesAfterStartedAt)) {
//            throw new \Exception(__('strings.match.not_in_ready_window'));
//        }
        $matchParticipantRecord = MatchParticipant::query()
            ->where('match_id', $match->id)
            ->where('participant_id', $participant->id)
            ->first();

        $readyStateChanged = false;
        if (!$matchParticipantRecord) {
            MatchParticipant::query()
                ->create([
                    'match_id' => $match->id,
                    'participant_id' => $participant->id,
                    'ready_at' => Carbon::now(),
                ]);
            $readyStateChanged = true;
        } else if (!$matchParticipantRecord->ready_at) {
            $matchParticipantRecord->ready_at = Carbon::now();
            $matchParticipantRecord->save();
            $readyStateChanged = true;
        }
        $lobby = $match->lobby;
        if (!$lobby) {
            throw new \Exception(__('strings.operation_cannot_be_done'));
        }
        if (!$readyStateChanged) {
            return $lobby;
        }

        $readyCount = MatchParticipant::query()
            ->where('match_id', $match->id)
            ->whereNotNull('ready_at')
            ->count();

        if ($readyCount == 1) {
            $lobbyRepository->createBigTitleMessage($lobby, 'Prematch prep');
        }

        $lobbyRepository->createReadyMessage($lobby, $user);

        if ($readyCount >= 2 && $readyCount == $match->getParticipants()->count()) {
//            sleep(1);
//            $lobbyRepository->createAutoCoinTossMessage($lobby);
//            sleep(1);
//            $lobbyRepository->creatPickAndBanFirstMessage($lobby);
            dispatch(new FireUpPrematchPreparation($lobby->id));
        }
        return $lobby;
    }

    /**
     * @param Tournament $tournament
     * @return int
     */
    private function getTournamentPartiesCount(Tournament $tournament)
    {
        $tournamentType = TournamentType::where('id', $tournament->tournament_type_id)->first();
        if ($tournamentType) {
            if (in_array($tournament->title, ['Single Elimination', 'Double Elimination', 'League'])) {
                return 2;
            }
            if (in_array($tournamentType->title, ['Round Robin', 'Battle Royale'])) {
                return $tournament->participants()->count();
            }
        }
        return 2;
    }

    /**
     * @param User $user
     * @param Match $match
     * @param $participants
     * @return array
     */
    private function getReadyState(User $user, Match $match, $participants)
    {
        $readyState = [
            'viewer_is_participant' => false,
        ];
        $viewerIsAParticipant = false;
        $participantIds = $participants->pluck('id')->toArray();
        foreach ($participants as $participant) {
            $type = $participant->participantable_type == Team::class ? 'team' : 'user';
            $playerIds = [];
            if ($type == 'team') {
                $playerIds = $participant->participantable->players->pluck('user_id')->toArray();
            } else if ($type == 'user') {
                $playerIds[] = $participant->participantable_id;
            }
            if (in_array($user->id, $playerIds)) {
                $viewerIsAParticipant = true;
                $viewerIsReady = MatchParticipant::query()
                        ->where('match_id', $match->id)
                        ->where('participant_id', $participant->id)
                        ->whereNotNull('ready_at')
                        ->count() > 0;
                $opponentIsReady = false;
                foreach ($participantIds as $participantId) {
                    if ($participantId == $participant->id) {
                        continue;
                    }
                    $opponentIsReady = MatchParticipant::query()
                            ->where('match_id', $match->id)
                            ->where('participant_id', $participantId)
                            ->whereNotNull('ready_at')
                            ->count() > 0;
                }
                $readyState['viewer_is_participant'] = $viewerIsAParticipant;
                $readyState['viewer_is_ready'] = $viewerIsReady;
                $readyState['opponent_is_ready'] = $opponentIsReady;
            }
        }
        return $readyState;
    }

    /**
     * @param Match $match
     * @param User $user
     * @return Participant|null
     */
    public function findMatchParticipantByUser(Match $match, User $user)
    {
        $matchParticipants = $match->getParticipants();
        foreach ($matchParticipants as $participant) {
            if ($participant->participantable_type == User::class) {
                if ($participant->participantable_id == $user->id) {
                    return $participant;
                }
            } else if ($participant->participantable_type == Team::class) {
                $team = Team::find($participant->participantable_id);
                $teamUserIds = $team->players->pluck('user_id')->all();
                if (in_array($user->id, $teamUserIds)) {
                    return $participant;
                }
            }
        }
        return null;
    }
}
