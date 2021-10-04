<?php

namespace App\Engines;


use App\Enums\ParticipantAcceptanceState;
use App\Exceptions\TournamentIsActiveException;
use App\Match;
use App\Participant;
use App\Party;
use App\Play;
use App\Repositories\LobbyRepository;
use App\Tournament;
use Illuminate\Database\Eloquent\Collection;

abstract class TournamentEngine
{

    /**
     * @var array
     */
    protected $matchesByNextMatch = [];

    /**
     * Number of participants in a match
     *
     * @return int
     */
    abstract public function matchPlayerCount();

    /**
     * Randomly assigns participants to the matches of the tournament
     *
     * @return mixed
     */
    abstract protected function randomlyAssignParticipantsToMatches();

    /**
     * Get next match object for the winner of the current match
     *
     * @param Match $match
     * @return null|Match
     */
    abstract public function getNextMatchForWinner(Match $match);

    /**
     * Create bracket for this tournament type
     */
    abstract public function createBracket();

    /**
     * Get next match object for the loser of the current match
     *
     * @param Match $match
     * @return null|Match
     */
    abstract public function getNextMatchForLoser(Match $match);

    /**
     * Get maximum number of games a team has to play to win the tournament
     *
     * @param Tournament $tournament
     * @return int
     */
    abstract public function getMaxRoundNumber(Tournament $tournament);

    /**
     * Get participant for the given rank
     *
     * @param int $rank
     * @return mixed|null
     */
    abstract public function getParticipantByRank(int $rank);

    /**
     * Get bracket for tournament
     *
     * @param Tournament $tournament
     * @return array
     */
    public function getBracket(Tournament $tournament)
    {
        $matches = $tournament->matches()
            ->orderBy('group')
            ->orderBy('round')
            ->orderBy('id')
            ->with('plays', 'plays.parties')
            ->get()
            ->all();
        $bracketMatches = [];
        $matchesByGroupAndRound = [];
        foreach ($matches as $match) {
            $currentMatchArray = $match->toArray();
            $currentMatchArray['winner_next_match_id'] = null;
            $currentMatchArray['loser_next_match_id'] = null;
            $winnerNextMatch = $this->getNextMatchForWinner($match);
            if ($winnerNextMatch) {
                $currentMatchArray['winner_next_match_id'] = $winnerNextMatch->id;
            }
            $loserNextMatch = $this->getNextMatchForLoser($match);
            if ($loserNextMatch) {
                $currentMatchArray['loser_next_match_id'] = $loserNextMatch->id;
            }
            $currentMatchArray['match_order_in_round'] = $this->getMatchIndexInRound($match, $tournament);
            $bracketMatches[] = $currentMatchArray;

            $matchesByGroupAndRound[$match->group][$match->round][] = $currentMatchArray;
        }

        $separatedMatches = [];
        foreach ($matchesByGroupAndRound as $group => $matchesByRound) {
            $sameGroupMatches = [
                'group' => $group,
                'rounds' => []
            ];
            foreach ($matchesByRound as $round => $matches) {
                $sameGroupMatches['rounds'][] = [
                    'round' => $round,
                    'matches' => $matches
                ];
            }
            $separatedMatches[] = $sameGroupMatches;
        }

        return [
            'tournament' => $tournament->toArray(),
            'matches' => $separatedMatches
        ];
    }

    /**
     * @param Match $match
     * @return Match[]|null
     */
    public function getPreviousMatches(Match $match)
    {
        if (!$this->matchesByNextMatch) {
            $this->getBracket($match->tournament);
        }
        if (array_key_exists($match->id, $this->matchesByNextMatch)) {
            return $this->matchesByNextMatch[$match->id];
        }
        return null;
    }

    /**
     * @param Match $match
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|Participant
     */
    public function getWinnerOfTheMatch(?Match $match)
    {
        if (!$match) {
            return null;
        }
        return $match->winner()
            ->with('participantable')
            ->first();
    }

    /**
     * @param Match $match
     * @return \Illuminate\Database\Eloquent\Builder[]|Collection|null
     */
    public function getLosersOfTheMatch(?Match $match)
    {
        if (!$match || !$match->winner_team_id) {
            return null;
        }
        $allParticipantsIds = $match->getParticipants()->pluck('id');
        $participantIds = [];
        foreach ($allParticipantsIds as $id) {
            if ($match->winner_team_id != $id) {
                $participantIds[] = $id;
            }
        }
        return Participant::query()
            ->whereIn('id', $participantIds)
            ->with('participantable')
            ->get();
    }

    /**
     * @param Tournament $tournament
     */
    public function setTournamentMatchesDate(Tournament $tournament)
    {
        $startTime = $tournament->started_at;
        $tournamentDuration = $tournament->started_at->diffInMinutes($tournament->ended_at);
        $maxNumberOfGames = $this->getMaxRoundNumber($tournament);
        $minMatchLength = $tournament->match_play_count * 30;
        $matchLength = intval($tournamentDuration / $maxNumberOfGames);
        $matchLength = max($minMatchLength, $matchLength);
        $firstRoundMatches = $tournament->matches()
            ->where('group', 1)
            ->where('round', 1)
            ->get();
        foreach ($firstRoundMatches as $match) {
            $this->setMatchDate($match, $startTime, $matchLength);
        }
    }

    /**
     * @param Match|null $match
     * @param \Carbon\Carbon $date
     * @param int $matchLength
     */
    private function setMatchDate(?Match $match, \Carbon\Carbon $date, int $matchLength)
    {
        if (!$match) {
            return;
        }
        if ($match->started_at == null || $match->started_at->timestamp < $date->timestamp) {
            $match->started_at = $date;
            $match->save();
            $nextMatchForWinner = $this->getNextMatchForWinner($match);
            $nextMatchForLoser = $this->getNextMatchForLoser($match);
            $nextDate = $date->clone();
            $nextDate->addMinutes($matchLength);
            $this->setMatchDate($nextMatchForWinner, $nextDate, $matchLength);
            $nextDate = $date->clone();
            $nextDate->addMinutes($matchLength);
            $this->setMatchDate($nextMatchForLoser, $nextDate, $matchLength);
        }
    }

    /**
     * Calculates number of rounds
     *
     * @param int $playerCount
     * @return int
     */
    protected function totalRounds(int $playerCount)
    {
        return intval(ceil(log($playerCount, 2)));
    }

    /**
     * If tournament does not have any match with
     * determined results delete all the matches
     *
     * @param Tournament $tournament
     * @throws TournamentIsActiveException
     */
    protected function removeEmptyBracket(Tournament $tournament)
    {
        $matches = $tournament
            ->matches()
            ->whereNotNull('winner_team_id')
            ->get();
        if ($matches->count() == 0) {
            $matches = $tournament->matches()->get();
            $this->removeMatches($matches);
        } else {
            throw new TournamentIsActiveException('Bracket is not empty');
        }
    }

    /**
     * Determines if tournament has any finished matches
     *
     * @param Tournament $tournament
     * @return bool
     */
    protected function bracketHasFinishedMatches(Tournament $tournament)
    {
        $matches = $tournament
            ->matches()
            ->whereNotNull('winner_team_id')
            ->get();
        return $matches->count() > 0;
    }

    /**
     * Delete provided matches with their relations
     *
     * @param Collection $matches
     */
    private function removeMatches(Collection $matches)
    {
        foreach ($matches as $match) {
            $match->delete();
        }
    }

    /**
     * Create a single match with it plays
     *
     * @param Tournament $tournament
     * @param int $round
     * @param int $group
     * @return Match
     */
    protected function createMatch(Tournament $tournament, int $round, int $group)
    {
        $match = new Match([
            'tournament_id' => $tournament->id,
            'round' => $round,
            'group' => $group,
            'play_count' => $tournament->match_play_count,
        ]);
        $match->save();
        $this->createPlaysForMatch($match);
        $lobbyRepository = new LobbyRepository();
        $lobbyRepository->createBy($match);
        return $match;
    }

    /**
     * Create plays of a match with their parties
     *
     * @param Match $match
     * @return array
     */
    protected function createPlaysForMatch(Match $match)
    {
        $playSequences = range(1, $match->play_count);
        $plays = [];
        foreach ($playSequences as $sequence) {
            $play = new Play([
                'match_id' => $match->id
            ]);
            $play->save();
            $plays[] = $play;
            $this->createPlayParties($play);
        }
        return $plays;
    }

    /**
     * Create parties of a play
     *
     * @param Play $play
     * @return array
     */
    protected function createPlayParties(Play $play)
    {
        $playPartiesCount = $this->matchPlayerCount();
        $parties = [];
        for ($i = 0; $i < $playPartiesCount; $i++) {
            $party = new Party([
                'play_id' => $play->id
            ]);
            $parties[] = $party->save();
        }
        return $parties;
    }

    /**
     * Number of participants in the given tournament
     *
     * @param Tournament $tournament
     * @return int
     */
    protected function participantCount(Tournament $tournament)
    {
        return $tournament
            ->participants()
            ->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])
            ->count();
    }

    /**
     * @param Tournament $tournament
     * @return Participant[]
     */
    protected function getAllParticipantsWithRandomOrder(Tournament $tournament)
    {
        return $tournament->participants()
            ->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])
            ->get()
            ->shuffle()
            ->all();
    }

    /**
     * @param Tournament $tournament
     * @param int $group
     * @return Match[]
     */
    protected function getFirstRoundMatchesForGroup(Tournament $tournament, int $group = 1)
    {
        return $tournament->matches()
            ->firstRoundOfGroup($group)
            ->get()
            ->all();
    }

    /**
     * @param Tournament $tournament
     * @return mixed
     */
    protected function getFirstRoundMatchesForAllGroups(Tournament $tournament)
    {
        return $tournament->matches()
            ->firstRoundOfAllGroups()
            ->get()
            ->all();
    }

    /**
     * @param Participant $participant
     */
    public function assignParticipantToFirstEmptyMatch(Participant $participant)
    {
        $tournament = $participant->tournament;
        $match = $this->getFirstEmptyMatchSlot($tournament);
        if ($match) {
            $plays = $match->plays;
            foreach ($plays as $play) {
                $parties = $play->parties;
                foreach ($parties as $party) {
                    if (!$party->team_id) {
                        $party->team_id = $participant->id;
                        $party->save();
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param Tournament $tournament
     * @return null|Match
     */
    public function getFirstEmptyMatchSlot(Tournament $tournament)
    {
        return $tournament->matches()
            ->firstRoundOfGroup(1)
            ->whereHas('plays', function ($plays) {
                return $plays->whereHas('parties', function ($parties) {
                    return $parties->whereNull('team_id');
                });
            })
            ->first();
    }

    /**
     * @param Match $match
     * @param Participant ...$participants
     */
    protected function assignParticipantsToMatch(Match $match, Participant ...$participants)
    {
        $plays = $match->plays->all();
        foreach ($plays as $play) {
            $this->assignParticipantsToPlay($play, $participants);
        }
    }

    /**
     * @param Match $match
     * @param Participant $participant
     * @return array
     */
    public function assignParticipantToMatch(Match $match, Participant $participant)
    {
        $plays = $match->plays->all();
        $parties = [];
        foreach ($plays as $play) {
            $parties[] = $this->assignParticipantToPlay($play, $participant);
        }
        return $parties;
    }

    /**
     * @param Play $play
     * @param Participant ...$participants
     */
    protected function assignParticipantsToPlay(Play $play, Participant ...$participants)
    {
        $parties = $play->parties->all();
        foreach ($participants as $index => $participant) {
            if (isset($parties[$index])) {
                $this->assignParticipantToParty($parties[$index], $participant);
            }
        }
    }

    /**
     * @param Play $play
     * @param Participant $participant
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    protected function assignParticipantToPlay(Play $play, Participant $participant)
    {
        $party = $play->parties()->whereNull('team_id')->first();
        if ($party) {
            $this->assignParticipantToParty($party, $participant);
            return $party;
        }
        return null;
    }

    /**
     * @param Party $party
     * @param Participant $participant
     */
    protected function assignParticipantToParty(Party $party, Participant $participant)
    {
        $party->setAttribute('team_id', $participant->id)->save();
    }

    /**
     * @param Match $match
     * @param Tournament $tournament
     * @return mixed
     */
    protected function getMatchIndexInRound(Match $match, Tournament $tournament)
    {
        $matchIndexInRound = Match::where('tournament_id', $tournament->id)
            ->where('group', $match->group)
            ->where('round', $match->round)
            ->where('id', '<=', $match->id)
            ->count();
        return $matchIndexInRound;
    }

    /**
     * @param Tournament $tournament
     * @param int $group
     * @param int $round
     * @param int $offset
     * @return mixed
     */
    protected function getMatchWithOffsetInRound(Tournament $tournament, int $group, int $round, int $offset)
    {
        $match = Match::where('tournament_id', $tournament->id)
            ->where('group', $group)
            ->where('round', $round)
            ->limit(1)
            ->offset($offset)
            ->first();
        return $match;
    }
}