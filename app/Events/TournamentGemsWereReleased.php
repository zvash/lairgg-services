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
     * @var int $teamId
     */
    public $teamId;

    /**
     * @var boolean $isGem
     */
    public $isGem;

    /**
     * TournamentGemsWereReleased constructor.
     * @param Tournament $tournament
     * @param bool $isGem
     * @param array $userIds
     * @param bool $isTeamTournament
     * @param int|null $teamId
     */
    public function __construct(Tournament $tournament, bool $isGem, array $userIds, bool $isTeamTournament, int $teamId = null)
    {
        $this->tournament = $tournament;
        $this->isGem = $isGem;
        $this->userIds = $userIds;
        $this->isTeamTournament = $isTeamTournament;
        $this->teamId = $teamId;
    }


}
