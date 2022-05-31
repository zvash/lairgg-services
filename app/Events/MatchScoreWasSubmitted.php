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

class MatchScoreWasSubmitted
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
     * MatchLobbyHadAnAction constructor.
     * @param Match $match
     * @param User $user
     */
    public function __construct(Match $match, User $user)
    {
        $this->match = $match;
        $this->user = $user;
    }
}
