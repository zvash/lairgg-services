<?php

namespace App\Events;

use App\Team;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamPlayersWereChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Team $team
     */
    public $team;

    /**
     * @var string $action
     */
    public $action;

    /**
     * @var User $user
     */
    public $user;

    /**
     * TeamPlayersWereChanged constructor.
     * @param Team $team
     * @param string $action
     * @param User $user
     */
    public function __construct(Team $team, string $action, User $user)
    {
        $this->team = $team;
        $this->action = $action;
        $this->user = $user;
    }


}
