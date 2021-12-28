<?php

namespace App\Engines;


use App\Match;
use App\Tournament;
use Illuminate\Support\Facades\DB;
use App\Exceptions\TournamentIsActiveException;

class DoubleEliminationEngine extends TournamentEngine
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
            $this->generateWinnerRounds();
            $this->generateLoserRounds();
            $this->generateGrandFinalRound();
            $this->setTournamentMatchesDate($this->tournament);
            $this->randomlyAssignParticipantsToMatches();
            $this->fillNextMatches();
            DB::commit();
        } catch (TournamentIsActiveException $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }
    }

    /**
     * @param Tournament $tournament
     * @return int|mixed
     */
    public function getMaxRoundNumber(Tournament $tournament)
    {
        return $tournament->matches()
            ->where('group', 2)
            ->max('round') + 2;
    }

    protected function generateWinnerRounds()
    {
        for ($round = 1; $round <= $this->rounds; $round++) {
            $this->createMatchesForWinnerRound($round);
        }
    }

    protected function generateLoserRounds()
    {
        for ($round = 1; $round < $this->rounds; $round++) {
            $this->createMatchesForLoserRound($round);
        }
    }

    protected function generateGrandFinalRound()
    {
        $this->createMatch($this->tournament, 1, 3);
    }

    /**
     * Number of participants in a match
     *
     * @return int
     */
    public function matchPlayerCount()
    {
        return 2;
    }

    /**
     * Randomly assigns participants to the matches of the tournament
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
        if ($match->group == 3) {
            return null;
        }

        if ($this->isFinalMatchOfGroup($match)) {
            $nextMatch = Match::where('tournament_id', $this->tournament->id)
                ->where('group', 3)
                ->first();
            if ($nextMatch) {
                $this->matchesByNextMatch[$nextMatch->id][] = $match;
            }

            return $nextMatch;
        }

        $nextRound = $match->round + 1;
        $index = $this->getMatchIndexInRound($match, $this->tournament);
        $offset = $index - 1;

        if ($match->group == 1) { //winners group

            $offset = intval(ceil($index / 2)) - 1;

        } else if ($match->group == 2) { //losers group

            if ($match->round % 2 == 0) {
                $offset = intval(ceil($index / 2)) - 1;
            }

        }

        $nextMatch = $this->getMatchWithOffsetInRound($this->tournament, $match->group, $nextRound, $offset);
        if ($nextMatch) {
            $this->matchesByNextMatch[$nextMatch->id][] = $match;
        }
        return $nextMatch;
    }

    /**
     * Get next match object for the loser of the current match
     *
     * @param Match $match
     * @return null|Match
     */
    public function getNextMatchForLoser(Match $match)
    {
        if (in_array($match->group, [2, 3])) {
            return null;
        }
        $losersGroup = 2;
        $losersRound = $this->mapWinnerRoundToLoserRound($match->round);
        $index = $this->getMatchIndexInRound($match, $this->tournament);
        $offset = $index - 1;
        if ($losersRound == 1) {
            //$offset = intval(ceil($index / 2)) - 1;
            $losersRoundGameCount = intval(pow(2, $this->rounds - 2));
            $offset = ($index <= $losersRoundGameCount ? $index : $index - $losersRoundGameCount) - 1;
        }
        $nextMatch = $this->getMatchWithOffsetInRound($this->tournament, $losersGroup, $losersRound, $offset);

        if ($nextMatch) {
            $this->matchesByNextMatch[$nextMatch->id][] = $match;
        }

        return $nextMatch;
    }

    /**
     * @param int $winnerRound
     * @return int|mixed
     */
    private function mapWinnerRoundToLoserRound(int $winnerRound)
    {
        return max(0, $winnerRound - 2) + $winnerRound;
    }

    /**
     * @param int $round
     * @return array
     */
    private function createMatchesForWinnerRound(int $round)
    {
        $matchCount = pow(2, $this->rounds - $round);
        $matches = [];
        for ($i = 0; $i < $matchCount; $i++) {
            $matches[] = $this->createMatch($this->tournament, $round, 1);
        }
        return $matches;
    }

    private function createMatchesForLoserRound(int $round)
    {
        $matchCount = pow(2, $this->rounds - $round) / 2;
        $firstRound = $round * 2 - 1;
        $secondRound = $round * 2;
        $matches = [];
        for ($i = 0; $i < $matchCount; $i++) {
            $matches[] = $this->createMatch($this->tournament, $firstRound, 2);
        }
        for ($i = 0; $i < $matchCount; $i++) {
            $matches[] = $this->createMatch($this->tournament, $secondRound, 2);
        }
        return $matches;
    }

    /**
     * @param Match $match
     * @return bool
     */
    private function isFinalMatchOfGroup(Match $match)
    {
        if ($match->group == 1) {
            return $match->round == $this->rounds;
        }
        if ($match->group == 2) {
            return $match->round == ($this->rounds - 1) * 2;
        }
        if ($match->group == 3) {
            return true;
        }
        return false;
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
            $match = $this->getGrandFinalMatch();
            if ($rank == 1) {
                return $this->getWinnerOfTheMatch($match);
            } else {
                $losers = $this->getLosersOfTheMatch($match);
                if ($losers) {
                    return $losers->first();
                }
                return null;
            }
        } else if ($rank > 2) {
            $match = $this->getLoserBracketMatchForRank($rank);
            if (! $match) {
                return null;
            }
            $losers = $this->getLosersOfTheMatch($match);
            if ($losers) {
                return $losers->first();
            }
            return null;
        }
        return null;
    }

    /**
     * @param Match $match
     * @return mixed|string
     */
    public function getRoundTitle(Match $match)
    {
        if ($match->group == 3) {
            return 'Grand Final';
        } else if ($match->group == 1) {
            $lastRound = $this->tournament->matches()
                ->where('group', 1)
                ->max('round');
            if ($match->round == $lastRound) {
                return 'Final';
            }
            if ($match->round == $lastRound - 1) {
                return 'Semifinals';
            }
            if ($match->round == $lastRound - 2) {
                return 'Quarterfinals';
            }
            return 'Round of ' . (pow(2, $lastRound + 1 - $match->round));
        } else if ($match->group == 2) {
            $lastRound = $this->tournament->matches()
                ->where('group', 1)
                ->max('round') ;
            $losersMaxRounds = ($lastRound - 1) * 2;
            if ($match->round == $losersMaxRounds) {
                return 'Loser\'s Final';
            } else {
                return 'Loser\'s Round ' . $match->round;
            }
        }
        return '';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|Match
     */
    private function getGrandFinalMatch()
    {
        return $this->tournament->matches()
            ->where('group', 3)
            ->where('round', 1)
            ->first();
    }

    /**
     * @param int $rank
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|Match
     */
    private function getLoserBracketMatchForRank(int $rank)
    {
        $lastRound = $this->tournament->matches()
            ->where('group', 2)
            ->max('round');
        $round = $lastRound - $rank + 3;
        return $this->tournament->matches()
            ->where('group', 2)
            ->where('round', $round)
            ->first();
    }
}
