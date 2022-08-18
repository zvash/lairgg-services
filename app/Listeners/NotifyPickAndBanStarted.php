<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\PickAndBanStarted;
use App\Traits\Notifications\ParticipantHelper;
use App\Traits\Notifications\SendHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyPickAndBanStarted
{
    use ParticipantHelper, SendHelper;

    /**
     * Handle the event.
     *
     * @param  PickAndBanStarted  $event
     * @return void
     */
    public function handle(PickAndBanStarted $event)
    {
        $match = $event->match;
        $template = 'notifications.match_lobby.pick_and_ban_started';
        $title = 'Pick & Ban';
        $body = __($template);
        $type = PushNotificationType::MATCH_LOBBY;
        $resourceId = $match->id;
        $image = $match->tournament->image;
        $participants = $match->getParticipants();
        $userIds = $this->getAllPlayersUserIdsFromParticipants($participants);
        $this->createAndSendNotifications($userIds, $type, $title, $body, $image, $resourceId);
    }
}
