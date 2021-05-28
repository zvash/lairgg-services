<?php

namespace App\Events;

use App\Invitation;
use App\User;
use Illuminate\Queue\SerializesModels;

class InvitationCreated
{
    use SerializesModels;

    public $invitation;
    public $invitee;

    /**
     * Create a new event instance.
     *
     * @param Invitation $invitation
     * @param User|null $invitee
     */
    public function __construct(Invitation $invitation, ?User $invitee = null)
    {
        $this->invitation = $invitation;
        $this->invitee = $invitee;
    }

}
