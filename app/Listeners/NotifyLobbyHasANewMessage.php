<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\LobbyHasANewMessage;
use App\Traits\Notifications\ParticipantHelper;
use App\Traits\Notifications\SendHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyLobbyHasANewMessage
{
    use ParticipantHelper, SendHelper;

    /**
     * Handle the event.
     *
     * @param  LobbyHasANewMessage  $event
     * @return void
     */
    public function handle(LobbyHasANewMessage $event)
    {
        $match = $event->match;
        $participant = $event->participant;
        $template = 'notifications.match_lobby.new_message';
        $title = 'New Message';
        $body = __($template);
        $type = PushNotificationType::MATCH_LOBBY;
        $resourceId = $match->id;
        $image = $participant->getAvatar();
        $userIds = $this->getCaptainUserIdOfOtherParticipants($match, $participant);
        $this->createAndSendNotifications($userIds, $type, $title, $body, $image, $resourceId);
    }
}
