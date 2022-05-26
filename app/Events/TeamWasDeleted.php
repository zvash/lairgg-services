<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamWasDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string $teamTitle
     */
    public $teamTitle;

    /**
     * @var string $image
     */
    public $image;

    /**
     * @var User $deleter
     */
    public $deleter;

    /**
     * @var array $userIds
     */
    public $userIds;

    /**
     * TeamWasDeleted constructor.
     * @param string $teamTitle
     * @param string|null $image
     * @param User $deleter
     * @param array $userIds
     */
    public function __construct(string $teamTitle, ?string $image, User $deleter, array $userIds)
    {
        $this->teamTitle = $teamTitle;
        $this->image = $image;
        $this->deleter = $deleter;
        $this->userIds = $userIds;
    }


}
