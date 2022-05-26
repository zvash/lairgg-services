<?php

namespace App\Observers;

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
        //
    }

    /**
     * Handle the tournament announcement "updated" event.
     *
     * @param  \App\TournamentAnnouncement  $tournamentAnnouncement
     * @return void
     */
    public function updated(TournamentAnnouncement $tournamentAnnouncement)
    {
        //
    }

    /**
     * Handle the tournament announcement "deleted" event.
     *
     * @param  \App\TournamentAnnouncement  $tournamentAnnouncement
     * @return void
     */
    public function deleted(TournamentAnnouncement $tournamentAnnouncement)
    {
        //
    }

    /**
     * Handle the tournament announcement "restored" event.
     *
     * @param  \App\TournamentAnnouncement  $tournamentAnnouncement
     * @return void
     */
    public function restored(TournamentAnnouncement $tournamentAnnouncement)
    {
        //
    }

    /**
     * Handle the tournament announcement "force deleted" event.
     *
     * @param  \App\TournamentAnnouncement  $tournamentAnnouncement
     * @return void
     */
    public function forceDeleted(TournamentAnnouncement $tournamentAnnouncement)
    {
        //
    }
}
