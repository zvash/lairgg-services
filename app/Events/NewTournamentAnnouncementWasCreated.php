<?php

namespace App\Events;

use App\TournamentAnnouncement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTournamentAnnouncementWasCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var TournamentAnnouncement $announcement
     */
    public $announcement;

    /**
     * NewTournamentAnnouncementWasCreated constructor.
     * @param TournamentAnnouncement $announcement
     */
    public function __construct(TournamentAnnouncement $announcement)
    {
        $this->announcement = $announcement;
    }


}
