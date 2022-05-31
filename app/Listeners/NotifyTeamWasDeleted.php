<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\TeamWasDeleted;
use App\PushNotification;
use App\Services\NotificationSender;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyTeamWasDeleted implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TeamWasDeleted  $event
     * @return void
     */
    public function handle(TeamWasDeleted $event)
    {
        $title = 'Team is Deleted';
        $body = __('notifications.message.team_deleted', [
            'team' => $event->teamTitle,
            'player' => $event->deleter->username,
        ]);
        $image = $event->image;
        $type = PushNotificationType::MESSAGE;
        $userIds = $event->userIds;

        $userIds = array_unique($userIds);

        foreach ($userIds as $userId) {
            PushNotification::query()->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'resource_id' => null,
                'payload' => null,
            ]);
        }

        $notStyledBody = str_replace('**', '"', $body);
        $pushService = new NotificationSender($title, $notStyledBody);
        $userIds[] = 0;
        $tokens = UserNotificationToken::query()
            ->whereIn('user_id', $userIds)
            ->get()
            ->pluck('token')
            ->all();
        if ($tokens) {
            $pushService->addTokens($tokens)->send();
        }
    }
}
