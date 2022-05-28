<?php

namespace App\Observers;

use App\Events\TournamentRulesWereUpdated;
use App\Tournament;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class TournamentObserver
{

    /**
     * Handle the User "created" event.
     *
     * @param  \App\Tournament  $tournament
     * @return void
     */
    public function created(Tournament $tournament)
    {
        $repository = new \App\Repositories\LobbyRepository();
        $repository->createBy($tournament);
    }
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
