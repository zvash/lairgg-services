<?php


namespace App\Traits\Notifications;


use App\Match;
use App\MatchParticipant;
use App\Participant;
use App\Team;
use App\User;
use Illuminate\Database\Eloquent\Collection;

trait ParticipantHelper
{
    /**
     * @param Collection $participants
     * @return array
     */
    private function getAllPlayersUserIdsFromParticipants(Collection $participants)
    {
        $userIds = [];
        foreach ($participants as $participant) {
            $userIds = array_merge($userIds, $this->getAllPlayersUserIdsFromParticipant($participant));
        }
        return array_unique($userIds);
    }

    /**
     * @param Participant $participant
     * @return array
     */
    private function getAllPlayersUserIdsFromParticipant(Participant $participant)
    {
        $userIds = [];
        if ($participant->participantable_type == User::class) {
            $userIds[] = $participant->participantable_id;
        } else if ($participant->participantable_type == Team::class) {
            $team = Team::find($participant->participantable_id);
            $userIds = $team->players->pluck('user_id')->all();
        }
        return $userIds;
    }

    /**
     * @param Match $match
     * @param Participant $participant
     * @return array
     */
    private function getCaptainUserIdOfOtherParticipants(Match $match, Participant $participant)
    {
        $otherMatchParticipants = MatchParticipant::query()
            ->where('match_id', $match->id)
            ->where('participant_id', '<>', $participant->id)
            ->whereNull('disqualified_at')
            ->get();
        $userIds = [];
        foreach ($otherMatchParticipants as $matchParticipant) {
            $captain = $matchParticipant->participant->getCaptain();
            $userIds[] = $captain->id;
        }
        return $userIds;
    }
}
