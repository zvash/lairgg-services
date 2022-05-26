<?php

namespace App\Observers;

use App\Events\TournamentRulesWereUpdated;
use App\Tournament;

class TournamentObserver
{

    /**
     * Handle the tournament "updating" event.
     *
     * @param  \App\Tournament  $tournament
     * @return void
     */
    public function updating(Tournament $tournament)
    {
        if ($tournament->isDirty('rules')) {
            event(new TournamentRulesWereUpdated($tournament));
        }
    }
}
