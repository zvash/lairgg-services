<?php

namespace App\Events;

use App\Match;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchLobbyHadAnAction
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Match $match
     */
    public $match;

    /**
     * @var User $user
     */
    public $user;

    /**
     * @var string $action
     */
    public $action;

    /**
     * MatchLobbyHadAnAction constructor.
     * @param Match $match
     * @param User $user
     * @param string $action
     */
    public function __construct(Match $match, User $user, string $action)
    {
        $this->match = $match;
        $this->user = $user;
        $this->action = $action;
    }


}
