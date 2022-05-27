<?php

namespace App\Listeners;

use App\Enums\CashOutStatus;
use App\Enums\PushNotificationType;
use App\Events\CashoutStatusWasChanged;
use App\PushNotification;
use App\Services\NotificationSender;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCashoutStatusWasChanged implements ShouldQueue
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
     * @param CashoutStatusWasChanged $event
     * @return void
     */
    public function handle(CashoutStatusWasChanged $event)
    {
        $template = 'notifications.cash_out.';
        if ($event->cashOut->status == CashOutStatus::ACCEPTED) {
            $template .= 'cash_out_approved';
        } else if ($event->cashOut->status == CashOutStatus::DENIED) {
            $template .= 'cash_out_denied';
        } else {
            return;
        }
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
