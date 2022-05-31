<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\MatchScoreWasSubmitted;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\User;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyMatchScoreWasSubmitted implements ShouldQueue
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
     * @param  MatchScoreWasSubmitted  $event
     * @return void
     */
    public function handle(MatchScoreWasSubmitted $event)
    {
        $match = $event->match;
        $template = 'notifications.match.score_submitted';
        $title = 'Score Submitted';
        $body = __($template, [
            'player' => $event->user->username,
        ]);
        $type = PushNotificationType::MATCH;
        $resourceId = $match->id;
        $image = $event->user->avatar;
        $userIds = [];

        $participantTitles = [];

        $participants = $match->getParticipants();
        foreach ($participants as $participant) {
            if ($participant->participantable_type == User::class) {
                $userIds[] = $participant->participantable_id;
                $participantTitles[] = User::find($participant->participantable_id)->username;
            } else if ($participant->participantable_type == Team::class) {
                $captainId = Team::find($participant->participantable_id)->players()->where('captain', 1)->get()->pluck('user_id')->first();
                if ($captainId) {
                    $userIds[] = $captainId;
                }
                $participantTitles[] = Team::find($participant->participantable_id)->title;
            }
        }

        $userIds = $this->removeItemFromArray($userIds, $event->user->id);

        $userIds = array_unique($userIds);

        foreach ($userIds as $userId) {
            PushNotification::query()->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'resource_id' => $resourceId,
                'payload' => ['participants_titles' => $participantTitles],
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
