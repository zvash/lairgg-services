<?php

namespace App\Listeners;

use App\Enums\PushNotificationType;
use App\Events\ParticipantIsDisqualified;
use App\MatchParticipant;
use App\Participant;
use App\Traits\Notifications\ParticipantHelper;
use App\Traits\Notifications\SendHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyParticipantIsDisqualified
{
    use ParticipantHelper, SendHelper;

    /**
     * Handle the event.
     *
     * @param  ParticipantIsDisqualified  $event
     * @return void
     */
    public function handle(ParticipantIsDisqualified $event)
    {
        $match = $event->match;
        $participant = $event->participant;

        $selfTemplate = 'notifications.match.disqualified';
        $opponentTemplate = 'notifications.match.opponent_disqualified';
        $title = 'Pre-match Preparation';
        $selfBody = __($selfTemplate);
        $opponentBody = __($opponentTemplate);
        $type = PushNotificationType::MATCH;
        $resourceId = $match->id;
        $image = $participant->getAvatar();

        $selfUserIds = $this->getAllPlayersUserIdsFromParticipant($participant);
        $this->createAndSendNotifications($selfUserIds, $type, $title, $selfBody, $image, $resourceId);

        $otherMatchParticipantsIds = MatchParticipant::query()
            ->where('match_id', $match->id)
            ->where('participant_id', '<>', $participant->id)
            ->whereNull('disqualified_at')
            ->pluck('participant_id')
            ->all();
        $otherMatchParticipantsIds[] = 0;

        $otherParticipants = Participant::query()
            ->whereIn('id', $otherMatchParticipantsIds)
            ->get();

        $otherUserIds = $this->getAllPlayersUserIdsFromParticipants($otherParticipants);
        $this->createAndSendNotifications($otherUserIds, $type, $title, $opponentBody, $image, $resourceId);
    }
}
