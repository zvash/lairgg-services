<?php


namespace App\Traits\Notifications;


use App\PushNotification;
use App\Services\NotificationSender;
use App\UserNotificationToken;

trait SendHelper
{
    /**
     * @param array $userIds
     * @param string $type
     * @param string $title
     * @param $body
     * @param $image
     * @param $resourceId
     */
    private function createAndSendNotifications(array $userIds, string $type, string $title, $body, $image, $resourceId): void
    {
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
