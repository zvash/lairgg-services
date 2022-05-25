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

class TournamentGemsWereReleased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Tournament $tournament
     */
    public $tournament;

    /**
     * @var array $userIds
     */
    public $userIds;

    /**
     * @var boolean $isTeamTournament
     */
    public $isTeamTournament;

    /**
     * TournamentGemsWereReleased constructor.
     * @param Tournament $tournament
     * @param array $userIds
     * @param bool $isTeamTournament
     */
    public function __construct(Tournament $tournament, array $userIds, bool $isTeamTournament)
    {
        $this->tournament = $tournament;
        $this->userIds = $userIds;
        $this->isTeamTournament = $isTeamTournament;
    }


}
