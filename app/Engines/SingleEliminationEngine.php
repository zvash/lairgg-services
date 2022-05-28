<?php

namespace App\Engines;


use App\Events\BracketWasReleased;
use App\Exceptions\TournamentIsActiveException;
use App\Match;
use App\Participant;
use App\Tournament;
use Illuminate\Support\Facades\DB;

class SingleEliminationEngine extends TournamentEngine
{
    /**
     * @var Tournament $tournament
     */
    protected $tournament;

    /**
     * @var int $rounds
     */
    protected $rounds;

    /**
     * @var array $bracket
     */
    protected $bracket = [];

    /**
     * @var array
     */
    protected $matchesByNextMatch = [];

    /**
     * SingleEliminationEngine constructor.
     * @param $tournament
     */
    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
        //$this->rounds = $this->totalRounds($this->participantCount($this->tournament));
        $this->rounds = $this->totalRounds($this->tournament->max_teams);
    }

    /**
     * Create bracket for this tournament type
     */
    public function createBracket()
    {
        try {
            DB::beginTransaction();
            $this->removeEmptyBracket($this->tournament);
            $this->generateRounds();
            $this->generateThirdRankMatchIfNeeded();
            $this->setTournamentMatchesDate($this->tournament);
            $this->randomlyAssignParticipantsToMatches();
            $this->fillNextMatches();
            DB::commit();
            event(new BracketWasReleased($this->tournament));
            return true;
        } catch (TournamentIsActiveException $exception) {
            DB::rollBack();
            return false;
            dd([$exception->getMessage(), 'TournamentIsActiveException']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
            dd($exception->getMessage(), 'Exception', $exception->getFile(), $exception->getLine());
        }
    }

    /**
     * @param Tournament $tournament
     * @return int|mixed
     */
    public function getMaxRoundNumber(Tournament $tournament)
    {
        return $tournament->matches()
                ->where('group', 1)
                ->max('round') - 1;
    }

    /**
     * @param int $round
     * @return array
     */
    private function createMatchesForRound(int $round)
    {
        $matchCount = pow(2, $this->rounds - $round);
        $matches = [];
        for ($i = 0; $i < $matchCount; $i++) {
            $matches[] = $this->createMatch($this->tournament, $round, 1);
        }
        return $matches;
    }

    /**
     * Iterate through rounds and add matches
     * for each round to the bracket
     */
    private function generateRounds(): void
    {
        for ($round = 1; $round <= $this->rounds; $round++) {
            $this->createMatchesForRound($round);
        }
    }

    /**
     * Check if tournament needs a match to determine
     * third rank team and if needed add its
     * match to the last round
     */
    private function generateThirdRankMatchIfNeeded(): void
    {
        if ($this->tournament->match_third_rank) {
            $this->createMatch($this->tournament, 1, 2);
        }
    }

    /**
     * Number of parties in a match
     *
     * @return int
     */
    public function matchPlayerCount()
    {
        return 2;
    }

    /**
     * Assigns participants to first round matches
     */
    protected function randomlyAssignParticipantsToMatches()
    {
        $tournament = $this->tournament;
        $participants = $this->getAllParticipantsWithRandomOrder($tournament);
        $firstRoundMatches = $this->getFirstRoundMatchesForGroup($tournament, 1);

        $matchPlayerCount = $this->matchPlayerCount();
        while ($matchPlayerCount > 0) {
            $matchPlayerCount--;
            foreach ($firstRoundMatches as $match) {
                if (count($participants)) {
                    $participant = array_shift($participants);
                    $this->assignParticipantToMatch($match, $participant);
                }
            }

        }
    }

    /**
     * Get next match object for the winner of the current match
     *
     * @param Match $match
     * @return null|Match
     */
    public function getNextMatchForWinner(Match $match)
    {
        $nextRound = $match->round + 1;
        if ($nextRound > $this->rounds) {
            return null;
        }
        $matchIndexInRound = $this->getMatchIndexInRound($match, $this->tournament);
        $nextMatchOffset = intval(ceil($matchIndexInRound / 2)) - 1;
        $nextMatch = Match::where('tournament_id', $this->tournament->id)
            ->where('group', $match->group)
            ->where('round', $nextRound)
            ->limit(1)
            ->offset($nextMatchOffset)
            ->first();

        if ($nextMatch) {
            $this->matchesByNextMatch[$nextMatch->id][] = $match;
        }

        return $nextMatch;
    }

    /**
     * Get next match for the loser of the current match
     *
     * @param Match $match
     * @return null
     */
    public function getNextMatchForLoser(Match $match)
    {
        if (
            $match->group == 1 &&
            $match->round == $this->rounds - 1 &&
            $this->tournament->match_third_rank
        ) {
            $nextMatch = Match::where('tournament_id', $this->tournament->id)
                ->where('group', 2)
                ->first();
            if ($nextMatch) {
                $this->matchesByNextMatch[$nextMatch->id][] = $match;
            }
            return $nextMatch;
        }
        return null;
    }

    /**
     * Get participant for the given rank
     *
     * @param int $rank
     * @return mixed|null
     */
    public function getParticipantByRank(int $rank)
    {
        if (in_array($rank, [1, 2])) {
            $match = $this->getFinalMatch();
            if ($rank == 1) {
                return $this->getWinnerOfTheMatch($match);
            } else {
                $losers = $this->getLosersOfTheMatch($match);
                if ($losers) {
                    return $losers->first();
                }
                return null;
            }
        } else if (in_array($rank, [3, 4])) {
            $match = $this->getThirdRankMatch();
            if (! $match) {
                return null;
            }
            if ($rank == 3) {
                return $this->getWinnerOfTheMatch($match);
            }  else {
                $losers = $this->getLosersOfTheMatch($match);
                if ($losers) {
                    return $losers->first();
                }
                return null;
            }
        }
        return null;
    }

    /**
     * @param Match $match
     * @return mixed|string
     */
    public function getRoundTitle(Match $match) {
        if ($match->group == 2) {
            return 'Third Rank Match';
        }
        $lastRound = $this->tournament->matches()
            ->where('group', 1)
            ->max('round');
        if ($match->round == $lastRound) {
            return 'Final';
        } else if ($match->round == $lastRound - 1) {
            return 'Semifinals';
        } else if ($match->round == $lastRound - 2) {
            return 'Quarterfinals';
        } else {
            $numberOfMatches = pow(2, $lastRound - $match->round);
            return 'Round of ' . ($numberOfMatches * 2);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|Match
     */
    private function getFinalMatch()
    {
        $lastRound = $this->tournament->matches()
            ->where('group', 1)
            ->max('round');
        return $this->tournament->matches()
            ->where('group', 1)
            ->where('round', $lastRound)
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|Match
     */
    private function getThirdRankMatch()
    {
        if ($this->tournament->match_third_rank) {
            return $this->tournament->matches()
                ->where('group', 2)
                ->where('round', 1)
                ->first();
        }
        return null;
    }
}
