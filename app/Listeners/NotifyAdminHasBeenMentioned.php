<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\AdminHasBeenMentioned;
use App\Traits\Notifications\SendHelper;
use App\User;
use App\UserLobby;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminHasBeenMentioned
{
    use SendHelper;

    /**
     * Handle the event.
     *
     * @param  AdminHasBeenMentioned  $event
     * @return void
     */
    public function handle(AdminHasBeenMentioned $event)
    {
        $match = $event->match;
        $template = 'notifications.match_lobby.admin_was_mentioned';
        $title = 'Admin Was Mentioned';
        $body = __($template);
        $type = PushNotificationType::MATCH_LOBBY;
        $resourceId = $match->id;
        $image = User::find($event->senderUserId)->avatar;
        $userIds = $event->staffIds;
//        foreach ($userIds as $userId) {
//            UserLobby::insertOrUpdate($userId, $match->lobby->name, false, true);
//        }
        $this->createAndSendNotifications($userIds, $type, $title, $body, $image, $resourceId);
    }
}
