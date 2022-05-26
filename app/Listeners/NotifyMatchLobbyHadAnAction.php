<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\MatchLobbyHadAnAction;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\User;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyMatchLobbyHadAnAction implements ShouldQueue
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
     * @param  MatchLobbyHadAnAction  $event
     * @return void
     */
    public function handle(MatchLobbyHadAnAction $event)
    {
        $match = $event->match;
        $template = 'notifications.match_lobby.' . $event->action;
        $title = 'Lobby';
        $body = __($template, [
            'player' => $event->user->username,
        ]);
        $type = PushNotificationType::MATCH_LOBBY;
        $resourceId = $match->id;
        $image = $event->user->avatar;
        $userIds = [];

        $participants = $match->getParticipants();
        foreach ($participants as $participant) {
            if ($participant->participantable_type == User::class) {
                $userIds[] = $participant->participantable_id;
            } else if ($participant->participantable_type == Team::class) {
                $captainId = Team::find($participant->participantable_id)->players()->where('captain', 1)->get()->pluck('user_id')->first();
                if ($captainId) {
                    $userIds[] = $captainId;
                }
            }
        }

        switch ($event->action) {
            case 'dispute_submitted':
            case 'coin_toss_request':
            case 'coin_toss_accepted':
            case 'coin_toss_declined':
                $userIds = $this->removeItemFromArray($userIds, $event->user->id);
                break;
            default:
                $image = null;
                break;
        }

        $userIds = array_unique($userIds);

        foreach ($userIds as $userId) {
            PushNotification::query()->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'resource_id' => $resourceId,
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

    /**
     * @param $arr
     * @param $item
     * @return mixed
     */
    private function removeItemFromArray($arr, $item)
    {
        if (($key = array_search($item, $arr)) !== false) {
            unset($arr[$key]);
        }
        return $arr;
    }
}
