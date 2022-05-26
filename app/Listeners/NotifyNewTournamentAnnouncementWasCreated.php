<?php

namespace App\Listeners;

use App\Enums\ParticipantAcceptanceState;
use App\Enums\PushNotificationType;
use App\Events\NewTournamentAnnouncementWasCreated;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\User;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyNewTournamentAnnouncementWasCreated implements ShouldQueue
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
     * @param  NewTournamentAnnouncementWasCreated  $event
     * @return void
     */
    public function handle(NewTournamentAnnouncementWasCreated $event)
    {
        $announcement = $event->announcement;
        $tournament = $announcement->tournament;
        $participants = $tournament->participants()->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])->get();
        $title = 'Tournament Bracket';
        $body = __('notifications.tournament.announcement', [
            'tournament' => $tournament->title,
        ]);
        $type = PushNotificationType::TOURNAMENT;
        $image = $tournament->image;
        $userIds = [];
        foreach ($participants as $participant) {
            if ($participant->participantable_type == User::class) {
                $userIds[] = $participant->participantable_id;
            } else if ($participant->participantable_type == Team::class) {
                $team = Team::find($participant->participantable_id);
                $teamUserIds = $team->players->pluck('user_id')->all();
                $userIds = array_merge($userIds, $teamUserIds);
            }
        }
        $userIds = array_unique($userIds);

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
