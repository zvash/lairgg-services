<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\CashoutWasCreated;
use App\PushNotification;
use App\Services\NotificationSender;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCashoutWasCreated implements ShouldQueue
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
     * @param  CashoutWasCreated  $event
     * @return void
     */
    public function handle(CashoutWasCreated $event)
    {
        $template = 'notifications.cash_out.place';
        $title = 'Cash Out';
        $body = __($template);
        $resourceId = $event->cashOut->id;
        $image = null;
        $type = PushNotificationType::CASH_OUT;
        $userIds = [$event->cashOut->user_id];

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
}
