<?php

namespace App\Observers;

use App\Events\NewTournamentAnnouncementWasCreated;
use App\TournamentAnnouncement;

class TournamentAnnouncementObserver
{
    /**
     * Handle the tournament announcement "created" event.
     *
     * @param  \App\TournamentAnnouncement  $tournamentAnnouncement
     * @return void
     */
    public function created(TournamentAnnouncement $tournamentAnnouncement)
    {
        event(new NewTournamentAnnouncementWasCreated($tournamentAnnouncement));
    }
}
