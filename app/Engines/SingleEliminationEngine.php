<?php

namespace App\Engines;


use App\Exceptions\TournamentIsActiveException;
use App\Match;
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
            $this->randomlyAssignParticipantsToMatches();
            DB::commit();
        } catch (TournamentIsActiveException $exception) {
            DB::rollBack();
            dd([$exception->getMessage(), 'TournamentIsActiveException']);
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage(), 'Exception', $exception->getFile(), $exception->getLine());
        }
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
    protected function matchPlayerCount()
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
}