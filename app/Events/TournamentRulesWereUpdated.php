<?php

namespace App\Events;

use App\Tournament;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TournamentRulesWereUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Tournament $tournament
     */
    public $tournament;

    /**
     * TournamentRulesWereUpdated constructor.
     * @param Tournament $tournament
     */
    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }


}
