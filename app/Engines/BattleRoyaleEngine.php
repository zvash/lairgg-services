<?php

namespace App\Engines;


use App\Match;
use App\Tournament;

class BattleRoyaleEngine extends TournamentEngine
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
        $this->rounds = 1;
    }

    /**
     * Number of participants in a match
     *
     * @return int
     */
    public function matchPlayerCount()
    {
        return $this->participantCount($this->tournament);
    }

    /**
     * @param Tournament $tournament
     * @return int|mixed
     */
    public function getMaxRoundNumber(Tournament $tournament)
    {
        return 1;
    }

    /**
     * Randomly assigns participants to the matches of the tournament
     *
     * @return mixed
     */
    protected function randomlyAssignParticipantsToMatches()
    {
        // TODO: Implement randomlyAssignParticipantsToMatches() method.
    }

    /**
     * Get next match object for the winner of the current match
     *
     * @param Match $match
     * @return null|Match
     */
    public function getNextMatchForWinner(Match $match)
    {
        return null;
    }

    /**
     * Create bracket for this tournament type
     */
    public function createBracket()
    {
        return false;
        // TODO: Implement createBracket() method.
    }

    /**
     * Get next match object for the loser of the current match
     *
     * @param Match $match
     * @return null|Match
     */
    public function getNextMatchForLoser(Match $match)
    {
        return null;
    }

    /**
     * Calculates number of rounds
     *
     * @param int $playerCount
     * @return int
     */
    protected function totalRounds(int $playerCount = 0)
    {
        return 1;
    }

    /**
     * Get participant for the given rank
     *
     * @param int $rank
     * @return mixed|null
     */
    public function getParticipantByRank(int $rank)
    {
        // TODO: Implement getParticipantByRank() method.
    }

    /**
     * @inheritDoc
     */
    public function getRoundTitle(Match $match)
    {
        return '';
    }
}
