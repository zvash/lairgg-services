<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\TeamPlayersWereChanged;
use App\PushNotification;
use App\Services\NotificationSender;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyTeamPlayersWereChanged implements ShouldQueue
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
     * @param  TeamPlayersWereChanged  $event
     * @return void
     */
    public function handle(TeamPlayersWereChanged $event)
    {
        $team = $event->team;
        $user = $event->user;
        $userIds = [];
        $template = "notifications.team.{$event->action}";
        $title = 'Team Changed';
        $body = __($template, [
            'team' => $team->title,
            'player' => $user->username,
        ]);
        $image = $team->logo;
        $type = PushNotificationType::TEAM;

        switch ($event->action) {
            case 'player_promoted':
            case 'player_removed':
                $userIds = [$user->id];
                break;
            case 'player_joined':
            case 'player_left':
                $userIds = $team->players->pluck('user_id')->all();
            default:
                $userIds = [];
        }

        $userIds = array_unique($userIds);

        foreach ($userIds as $userId) {
            PushNotification::query()->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'resource_id' => $team->id,
                'payload' => null,
            ]);
        }

        $notStyledBody = str_replace('**', '', $body);
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
