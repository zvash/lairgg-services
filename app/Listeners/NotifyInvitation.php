<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\InvitationCreated;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\Tournament;
use App\User;
use App\UserNotificationToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyInvitation implements ShouldQueue
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
     * @param InvitationCreated $event
     * @return void
     */
    public function handle(InvitationCreated $event)
    {
        $invitation = $event->invitation;
        $user = User::where('email', $event->invitation->email)->first();
        if (!$user) {
            return;
        }
        $title = '';
        $body = '';
        $image = null;
        $type = PushNotificationType::MESSAGE;
        if ($invitation->invite_aware_type == Tournament::class) {
            $tournament = Tournament::find($invitation->invite_aware_id);
            $title = 'Tournament Invitation';
            $body = __('notifications.tournament_invitation', ['tournament' => $tournament->title]);
            $image = $tournament->image;
            $type = PushNotificationType::TOURNAMENT_INVITATION;
        } else if ($invitation->invite_aware_type == Team::class) {
            $team = Team::find($invitation->invite_aware_id);
            $title = 'Team Invitation';
            $body = __('notifications.team_invitation', ['team' => $team->title]);
            $image = $team->logo;
            $type = PushNotificationType::TEAM_INVITATION;
        }

        PushNotification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'image' => $image,
            'resource_id' => $invitation->invite_aware_id,
            'payload' => null,
        ]);

        $notStyledBody = str_replace('**', '', $body);
        $pushService = new NotificationSender($title, $notStyledBody);
        $tokens = UserNotificationToken::query()
            ->where('user_id', $user->id)
            ->get()
            ->pluck('token')
            ->all();
        if ($tokens) {
            $pushService->addTokens($tokens)->send();
        }
    }
}
