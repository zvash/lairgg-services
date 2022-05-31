<?php

namespace App\Listeners;

use App\Enums\ParticipantAcceptanceState;
use App\Enums\PushNotificationType;
use App\Events\ParticipantStatusWasUpdated;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\User;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyParticipantJoinRequestWasRejected implements ShouldQueue
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
     * @param  ParticipantStatusWasUpdated  $event
     * @return void
     */
    public function handle(ParticipantStatusWasUpdated $event)
    {
        $participant = $event->participant;
        $tournament = $participant->tournament;
        if ($participant->status != ParticipantAcceptanceState::REJECTED) {
            return;
        }
        $title = 'Declined';
        $body = '';
        $type = PushNotificationType::TOURNAMENT;
        $image = $tournament->image;
        $userIds = [];
        if ($participant->participantable_type == User::class) {
            $userIds[] = $participant->participantable_id;
            $body = __('notifications.tournament.join_request_declined', [
                'tournament' => $tournament->title,
            ]);
        } else if ($participant->participantable_type == Team::class) {
            $team = Team::find($participant->participantable_id);
            $userIds = $team->players->pluck('user_id')->all();
            $body = __('notifications.tournament.join_team_request_declined', [
                'tournament' => $tournament->title,
                'team' => $team->title,
            ]);
        }

        foreach ($userIds as $userId) {
            PushNotification::query()->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'resource_id' => $tournament->id,
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
