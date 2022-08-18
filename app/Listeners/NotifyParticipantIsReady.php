<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\ParticipantIsReady;
use App\MatchParticipant;
use App\Participant;
use App\Team;
use App\Traits\Notifications\ParticipantHelper;
use App\Traits\Notifications\SendHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyParticipantIsReady
{
    use ParticipantHelper, SendHelper;

    /**
     * Handle the event.
     *
     * @param  ParticipantIsReady  $event
     * @return void
     */
    public function handle(ParticipantIsReady $event)
    {
        $match = $event->match;
        $participant = $event->participant;

        $template = 'notifications.match.opponent_ready';
        $title = 'Opponent is Ready';
        $body = __($template);
        $type = PushNotificationType::MATCH;
        $resourceId = $match->id;
        $image = $participant->getAvatar();

        $otherMatchParticipantsIds = MatchParticipant::query()
            ->where('match_id', $match->id)
            ->where('participant_id', '<>', $participant->id)
            ->whereNull('ready_at')
            ->whereNull('disqualified_at')
            ->pluck('participant_id')
            ->all();
        $otherMatchParticipantsIds[] = 0;

        $otherParticipants = Participant::query()
            ->whereIn('id', $otherMatchParticipantsIds)
            ->get();

        $userIds = $this->getAllPlayersUserIdsFromParticipants($otherParticipants);
        $this->createAndSendNotifications($userIds, $type, $title, $body, $image, $resourceId);
    }
}
