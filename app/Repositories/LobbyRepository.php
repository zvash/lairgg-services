<?php

namespace App\Repositories;


use App\Enums\ParticipantAcceptanceState;
use App\Lobby;
use App\Match;
use App\Team;
use App\Tournament;
use App\User;

class LobbyRepository extends BaseRepository
{

    protected $modelClass = Lobby::class;

    /**
     * @param \App\Tournament|\App\Match $owner
     * @param bool $isActive
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function createBy($owner, bool $isActive = true)
    {
        $attributes['lobby_aware_type'] = get_class($owner);
        $attributes['lobby_aware_id'] = $owner->id;
        $attributes['name'] = 'lobby_' . make_random_hash();
        $attributes['is_active'] = $isActive;
        $lobby = Lobby::query()->with('lobby_aware_type', $attributes['lobby_aware_type'])
            ->where('lobby_aware_id', $attributes['lobby_aware_id'])
            ->first();
        if ($lobby) {
            return $lobby;
        }

        try {
            return Lobby::create($attributes);
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return $this->createBy($owner, $isActive);
            }
            throw $e;
        }
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @return bool
     */
    public function userHasAccessToLobby(User $user, Lobby $lobby)
    {
        $owner = $lobby->owner;
        if ($owner instanceof Tournament) {
            return $this->userHasAccessToTournamentLobby($user, $owner);
        } else if ($owner instanceof Match) {
            $staffUserIds = $owner->tournament->organization->staff->pluck('user_id')->toArray();
            if (in_array($user->id, $staffUserIds)) {
                return true;
            }
            //TODO: who are match participants?
        }
        return false;
    }

    /**
     * @param User $user
     * @param Tournament $owner
     * @return bool
     */
    private function userHasAccessToTournamentLobby(User $user, Tournament $owner): bool
    {
        $staffUserIds = $owner->organization->staff->pluck('user_id')->toArray();
        if (in_array($user->id, $staffUserIds)) {
            return true;
        }
        $participants = $owner
            ->participants()
            ->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])
            ->get();
        $participantsUserIds = [];
        foreach ($participants as $participant) {
            $participantable = $participant->participantable;
            if ($participantable instanceof User) {
                $participantsUserIds[] = $participantable->id;
            } else if ($participantable instanceof Team) {
                $players = $participantable->players;
                foreach ($players as $player) {
                    $participantsUserIds[] = $player->user_id;
                }
            }
        }
        if (in_array($user->id, $participantsUserIds)) {
            return true;
        }
        return false;
    }

}