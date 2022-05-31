<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\TeamGemsWereShared;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyTeamGemsWereShared implements ShouldQueue
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
     * @param  TeamGemsWereShared  $event
     * @return void
     */
    public function handle(TeamGemsWereShared $event)
    {
        $teamTitle = $event->team->title;
        $slices = $event->sharedSlices;
        foreach ($slices as $userId => $amount) {
            if (!$amount || $amount*1 == 0) {
                continue;
            }
            $subject = 'Gems were shared!';
            $body = __('notifications.gem.gems_from_team', [
                'amount' => $amount,
                'team' => $teamTitle,
            ]);
            $notStyledBody = str_replace('**', '"', $body);

            PushNotification::query()->create([
                'user_id' => $userId,
                'type' => PushNotificationType::GEM,
                'title' => $subject,
                'body' => $body,
                'image' => $event->team->logo,
                'resource_id' => null,
                'payload' => null,
            ]);

            $pushService = new NotificationSender($subject, $notStyledBody);
            $tokens = UserNotificationToken::query()
                ->where('user_id', $userId)
                ->get()
                ->pluck('token')
                ->all();
            if ($tokens) {
                $pushService->addTokens($tokens)->send();
            }
        }
    }
}
