<?php

namespace App\Mail;

use App\Invitation;
use App\Team;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteToTeamMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject = 'Lair.GG Team Invitation';

    protected $invitation;

    protected $newUser;

    /**
     * Create a new message instance.
     *
     * @param Invitation $invitation
     * @param bool $newUser
     */
    public function __construct(Invitation $invitation, bool $newUser)
    {
        $this->invitation = $invitation;
        $this->newUser = $newUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $params = [
            'username' => User::find($this->invitation->invited_by)->username,
            'team' => Team::find($this->invitation->invite_aware_id)
        ];
        if ($this->newUser) {
            return $this->markdown('emails.team_invite_new_user', $params);
        }
        return $this->markdown('emails.team_invite_existing_user', $params);
    }
}
