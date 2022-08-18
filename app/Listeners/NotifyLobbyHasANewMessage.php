<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\LobbyHasANewMessage;
use App\Participant;
use App\Traits\Notifications\ParticipantHelper;
use App\Traits\Notifications\SendHelper;
use App\User;
use App\UserLobby;
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
        $otherParticipants = Participant::query()->whereIn('id', $event->participantIds)->get();
        $template = 'notifications.match_lobby.new_message';
        $title = 'New Message';
        $body = __($template);
        $type = PushNotificationType::MATCH_LOBBY;
        $resourceId = $match->id;
        $image = User::find($event->senderUserId)->avatar;
        $userIds = $this->getCaptainUserIdsForParticipants($otherParticipants);
        foreach ($userIds as $userId) {
            UserLobby::insertOrUpdate($userId, $match->lobby->name, false, true);
        }
        $this->createAndSendNotifications($userIds, $type, $title, $body, $image, $resourceId);
    }
}
