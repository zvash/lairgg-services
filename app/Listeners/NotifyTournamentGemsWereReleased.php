<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\TournamentGemsWereReleased;
use App\PushNotification;
use App\Services\NotificationSender;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyTournamentGemsWereReleased implements ShouldQueue
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
     * @param  TournamentGemsWereReleased  $event
     * @return void
     */
    public function handle(TournamentGemsWereReleased $event)
    {
        $tournamentTitle = $event->tournament->title;
        $notificationTemplate = 'notifications.prize.prize_from_tournament';
        if ($event->isTeamTournament) {
            $notificationTemplate = 'notifications.prize.prize_for_team';
        }
        $subject = 'You won a prize!';
        $body = __($notificationTemplate, [
            'tournament' => $tournamentTitle,
        ]);
        $notStyledBody = str_replace('*', '', $body);

        foreach ($event->userIds as $userId) {
            PushNotification::query()->create([
                'user_id' => $userId,
                'type' => PushNotificationType::PRIZE,
                'title' => $subject,
                'body' => $body,
                'image' => $event->tournament->logo,
                'resource_id' => $event->tournament->id,
                'payload' => null,
            ]);
        }

        $pushService = new NotificationSender($subject, $notStyledBody);
        $tokens = UserNotificationToken::query()
            ->whereIn('user_id', $event->userIds)
            ->get()
            ->pluck('token')
            ->all();
        if ($tokens) {
            $pushService->addTokens($tokens)->send();
        }
    }
}
