<?php

namespace App\Engines;


use App\Tournament;
use App\TournamentType;

class BracketCreator
{
    protected $tournament;

    protected $engine;

    /**
     * BracketEngine constructor.
     * @param Tournament $tournament
     */
    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * @return array|null
     */
    public function createBracket()
    {
        $engine = $this->engineSelector();
        if ($engine) {
            $engine->createBracket();
            return $this->getBracket();
        }
        return null;
    }

    /**
     * @return array|null
     */
    public function getBracket()
    {
        $engine = $this->engineSelector();
        if ($engine) {
            return $engine->getBracket($this->tournament);
        }
        return null;
    }

    protected function engineSelector()
    {
        $tournamentType = TournamentType::where('id', $this->tournament->tournament_type_id)->first();
        if ($tournamentType->title == 'Single Elimination') {
            return new SingleEliminationEngine($this->tournament);
        }
        if ($tournamentType->title == 'Double Elimination') {
            return new DoubleEliminationEngine($this->tournament);
        }
        if ($tournamentType->title == 'Battle Royale') {
            return new BattleRoyaleEngine($this->tournament);
        }
        return null;
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
}