<?php

namespace App\Events;

use App\Team;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamGemsWereShared
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Team $team
     */
    public $team;

    /**
     * @var array $sharedSlices
     */
    public $sharedSlices;

    /**
     * Create a new event instance.
     *
     * @param Team $team
     * @param array $sharedSlices
     */
    public function __construct(Team $team, array $sharedSlices)
    {
        $this->team = $team;
        $this->sharedSlices = $sharedSlices;
    }
}
