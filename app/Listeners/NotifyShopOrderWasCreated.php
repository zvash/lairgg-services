<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\ShopOrderWasCreated;
use App\PushNotification;
use App\Services\NotificationSender;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyShopOrderWasCreated implements ShouldQueue
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
     * @param  ShopOrderWasCreated  $event
     * @return void
     */
    public function handle(ShopOrderWasCreated $event)
    {

        $template = 'notifications.shop.place';
        $title = 'Shop';
        $body = __($template);
        $resourceId = $event->order->id;
        $image = $event->order->product->image;
        $type = PushNotificationType::SHOP;
        $userIds = [$event->order->user_id];

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
