<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Mail\InviteToTournamentMail;
use App\Tournament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;


class EmailInvitation implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  InvitationCreated  $event
     * @return void
     */
    public function handle(InvitationCreated $event)
    {
        $invitation = $event->invitation;
        $invitee = $event->invitee;
        if ($invitation->invite_aware_type == Tournament::class) {
            Mail::to($invitation->email)->send(new InviteToTournamentMail($invitation, $invitee == null));
        }
    }
}
