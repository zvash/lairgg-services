<?php

namespace App\Engines;


use App\Tournament;

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

    protected function engineSelector()
    {

    }
}