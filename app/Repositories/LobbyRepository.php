<?php

namespace App\Repositories;


use App\Dispute;
use App\Enums\DisputeState;
use App\Enums\ParticipantAcceptanceState;
use App\Http\Requests\CreateDisputeRequest;
use App\Lobby;
use App\LobbyMessage;
use App\Match;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

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
        $lobby = Lobby::query()
            ->where('lobby_aware_type', $attributes['lobby_aware_type'])
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
    public function userIsAnOrganizerForLobby(User $user, Lobby $lobby)
    {
        $tournament = $this->getTournamentOfLobby($lobby);
        $staffUserIds = $tournament->organization->staff->pluck('user_id')->toArray();
        return in_array($user->id, $staffUserIds);
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @return mixed
     */
    public function getUserTeamInLobby(User $user, Lobby $lobby)
    {
        $userTeamIds = $user->teams()->pluck('team_id')->toArray();
        if ($userTeamIds) {
            $tournament = $this->getTournamentOfLobby($lobby);
            $participant = $tournament->participants()
                ->where('participantable_type', Team::class)
                ->whereIn('participantable_id', $userTeamIds)
                ->first();
            if ($participant) {
                return Team::find($participant->participantable_id);
            }
        }
        return null;

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
            return $this->issuerIsAParticipant($user, $lobby);
        }
        return false;
    }

    /**
     * @param Lobby $lobby
     * @return bool
     */
    public function isMatchLobby(Lobby $lobby)
    {
        return $lobby->owner instanceof Match;
    }

    public function issuerIsAParticipant(User $user, Lobby $lobby)
    {
        if ($this->isMatchLobby($lobby)) {
            $owner = $lobby->owner;
            $participants = $owner->getParticipants();
            foreach ($participants as $participant) {
                if ($participant->participantable_type == User::class) {
                    if ($participant->participantable_id == $user->id) {
                        return true;
                    }
                }
            }
            foreach ($participants as $participant) {
                if ($participant->participantable_type == Team::class) {
                    $captain = Team::find($participant->participantable_id )->players()->whereCaptain(1)->first();
                    if ($captain && $captain->user_id == $user->id) {
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }

    public function creatCoinTossMessage(User $user, Lobby $lobby, string $title)
    {
        if (! $this->isMatchLobby($lobby)) {
            return null;
        }
        $match = $lobby->owner;
        $opponentCaptain = $this->getOpponent($user, $match);
        $opponentCaptainId = 0;
        if ($opponentCaptain) {
            $opponentCaptainId = $opponentCaptain->id;
        }
        $user = $this->prepareUserObjectForLobby($user, $lobby);
        $timestamp = time();
        $uuid = Str::orderedUuid()->toString();
        $newMessage = [
            'type' => 'coin_toss',
            'user' => $user->toArray(),
            'timestamp' => $timestamp,
            'text' => $title,
            'uuid' => $uuid,
            'lobby_name' => $lobby->name,
            'is_final' => false,
            'winner' => null,
            'loser' => null,
            'status' => 'pending',
            'actions_are_visible_to' => $opponentCaptainId,
            'actions' => [
                [
                    'title' => 'Decline',
                    'action' => "api/v1/lobbies/{$lobby->name}/coin-toss/$uuid/decline",
                ],[
                    'title' => 'Accept',
                    'action' => "api/v1/lobbies/{$lobby->name}/coin-toss/$uuid/accept",
                ]
            ],
        ];
        $lobbyMessageAttributes = [
            'uuid' => $uuid,
            'lobby_id' => $lobby->id,
            'user_id' => $user->id,
            'lobby_name' => $lobby->name,
            'type' => 'coin_toss',
            'sequence' => $this->getNextSequence($lobby),
            'sent_at' => date('Y-m-d H:i:s', intval($timestamp / 1000)),
            'message' => json_encode($newMessage),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        Redis::publish('lobby-server-message-channel', json_encode($newMessage));
        return $lobbyMessage->uuid;
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @param bool $backward
     * @param int $from
     * @return array
     */
    public function getMessages(User $user, Lobby $lobby, bool $backward = true, $from = 0)
    {
        $query = $lobby->messages();
        if ($from) {
            if ($backward) {
                $query->whereSequence('sequence', '<', $from);
            } else {
                $query->whereSequence('sequence', '>', $from);
            }
        } else {
            $query->latest();
        }
        $lobbyMessages = $query->limit(20)->get();
        $usersById = [];
        $teamsById = [];
        $messages = [];
        foreach ($lobbyMessages as $lobbyMessage) {
            $message = json_decode($lobbyMessage->message, 1);
            if (!array_key_exists($lobbyMessage->user_id, $usersById)) {
                $usersById[$lobbyMessage->user_id] = User::find($lobbyMessage->user_id);
            }
            if (isset($message['user']['team']) && !array_key_exists($message['user']['team']['id'], $teamsById)) {
                $teamsById[$message['user']['team']['id']] = Team::find($message['user']['team']['id']);
            }
            $message['user']['first_name'] = $usersById[$lobbyMessage->user_id]->first_name;
            $message['user']['last_name'] = $usersById[$lobbyMessage->user_id]->last_name;
            if ($usersById[$lobbyMessage->user_id]->avatar) {
                $message['user']['avatar'] = rtrim(env('AWS_URL'), '/') . '/' . $usersById[$lobbyMessage->user_id]->avatar;
            }

            if (isset($message['user']['team'])) {
                $message['user']['team']['title'] = $teamsById[$message['user']['team']['id']]->title;
                if ($teamsById[$message['user']['team']['id']]->logo) {
                    $message['user']['team']['logo'] = rtrim(env('AWS_URL'), '/') . '/' . $teamsById[$message['user']['team']['id']]->logo;
                }
                if ($teamsById[$message['user']['team']['id']]->cover) {
                    $message['user']['team']['cover'] = rtrim(env('AWS_URL'), '/') . '/' . $teamsById[$message['user']['team']['id']]->cover;
                }
            }
            $messages[] = $message;
        }
        return array_reverse($messages);
    }

    /**
     * @param Lobby $lobby
     * @return mixed
     */
    public function getNextSequence(Lobby $lobby)
    {
        $lastSequence = $this->getLastSequenceForLobby($lobby);
        $script = base_path('resources/lua_scripts/sequence_assigner.lua');
        $key = env('REDIS_PREFIX', 'lairgg_') . $lobby->name . '-sequence';
        return Redis::eval(file_get_contents($script), 1, $key, $lastSequence);
    }

    /**
     * @param Lobby $lobby
     */
    public function resetLastSequenceForLobby(Lobby $lobby)
    {
        $lastSequence = $this->getLastSequenceForLobby($lobby);
        $key = env('REDIS_PREFIX', 'lairgg_') . $lobby->name . '-sequence';
        Redis::set($key, $lastSequence);
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @param string $uuid
     * @return bool|null
     */
    public function acceptCoinToss(User $user, Lobby $lobby, string $uuid)
    {
        $lobbyMessage = $this->getCoinTossMessage($lobby, $uuid);
        if ($lobbyMessage) {
            $message = json_decode($lobbyMessage->message, 1);
            if ($message['actions_are_visible_to'] == $user->id && $message['status'] == 'pending') {
                $message['status'] = 'accepted';
                $message['is_final'] = true;
                $message['actions'] = [];
                $message['actions_are_visible_to'] = 0;
                $isWinner = mt_rand(0, 1) == 1;
                $match = $lobby->owner;
                $tosserResult = [
                    'user_id' => null,
                    'username' => null,
                    'team_id' => null,
                    'team_name' => null,
                ];
                $opponentResult = [
                    'user_id' => null,
                    'username' => null,
                    'team_id' => null,
                    'team_name' => null,
                ];
                $tosser = $user;
                $tosserTeam = $this->getUserTeamInLobby($tosser, $lobby);
                $opponent = $this->getOpponent($tosser, $match);
                $opponentTeam = $this->getUserTeamInLobby($opponent, $lobby);

                $tosserResult['user_id'] = $tosser->id;
                $tosserResult['username'] = $tosser->username;
                if ($tosserTeam) {
                    $tosserResult['team_id'] = $tosserTeam->id;
                    $tosserResult['team_name'] = $tosserTeam->title;
                }

                $opponentResult['user_id'] = $opponent->id;
                $opponentResult['username'] = $opponent->username;
                if ($opponentTeam) {
                    $opponentResult['team_id'] = $opponentTeam->id;
                    $opponentResult['team_name'] = $opponentTeam->title;
                }

                if ($isWinner) {
                    $message['winner'] = $tosserResult;
                    $message['loser'] = $opponentResult;
                } else {
                    $message['winner'] = $opponentResult;
                    $message['loser'] = $tosserResult;
                }
                $lobbyMessage->message = json_encode($message);
                $lobbyMessage->save();
                Redis::publish('lobby-server-edit-message-channel', $lobbyMessage->message);
                return $isWinner;
            }
        }
        return null;
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @param string $uuid
     * @return |null
     */
    public function rejectCoinToss(User $user, Lobby $lobby, string $uuid)
    {
        $lobbyMessage = $this->getCoinTossMessage($lobby, $uuid);
        if ($lobbyMessage) {
            $message = json_decode($lobbyMessage->message, 1);
            if ($message['actions_are_visible_to'] == $user->id && $message['status'] == 'pending') {
                $message['status'] = 'rejected';
                $message['is_final'] = true;
                $message['actions'] = [];
                $message['actions_are_visible_to'] = 0;
                $lobbyMessage->message = json_encode($message);
                $lobbyMessage->save();
                Redis::publish('lobby-server-edit-message-channel', $lobbyMessage->message);
                return $lobbyMessage;
            }
        }
        return null;
    }

    public function createDisputeFromRequest(CreateDisputeRequest $request, Lobby $lobby)
    {
        $user = $request->user();
        $inputs['title'] = $request->get('title');
        $screenshot = $this->saveImageFromRequest($request, 'screenshot', 'disputes/screenshots');
        if ($screenshot) {
            $inputs['screenshot'] = $screenshot;
        }
        $user = $this->prepareUserObjectForLobby($user, $lobby);
        $timestamp = time();
        $uuid = Str::orderedUuid()->toString();
        $newMessage = [
            'type' => 'dispute',
            'user' => $user->toArray(),
            'timestamp' => $timestamp,
            'text' => $inputs['title'],
            'screenshot' => isset($inputs['screenshot']) ? $this->makeFullUrl($inputs['screenshot']) : null,
            'uuid' => $uuid,
            'lobby_name' => $lobby->name,
            'status' => DisputeState::OPEN,
        ];
        $lobbyMessageAttributes = [
            'uuid' => $uuid,
            'lobby_id' => $lobby->id,
            'user_id' => $user->id,
            'lobby_name' => $lobby->name,
            'type' => 'dispute',
            'sequence' => $this->getNextSequence($lobby),
            'sent_at' => date('Y-m-d H:i:s', intval($timestamp / 1000)),
            'message' => json_encode($newMessage),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        $dispute = Dispute::query()->create([
            'match_id' => $lobby->owner->id,
            'lobby_message_id' => $lobbyMessage->id,
            'issued_by' => $user->id,
            'text' => $inputs['title'],
            'screenshot' => isset($inputs['screenshot']) ? $screenshot : null,
        ]);
        Redis::publish('lobby-server-message-channel', json_encode($newMessage));
        return $dispute;
    }

    /**
     * @param Lobby $lobby
     * @param string $uuid
     * @return mixed
     */
    private function getCoinTossMessage(Lobby $lobby, string $uuid)
    {
        return $lobby->messages()->whereUuid($uuid)->whereType('coin_toss')->first();
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

    /**
     * @param Lobby $lobby
     * @return Tournament
     */
    private function getTournamentOfLobby(Lobby $lobby)
    {
        $owner = $lobby->owner;
        $tournament = $owner;
        if ($owner instanceof Match) {
            $tournament = $owner->tournament;
        }
        return $tournament;
    }

    /**
     * @param Lobby $lobby
     * @return int|mixed
     */
    private function getLastSequenceForLobby(Lobby $lobby)
    {
        $lastSequence = 0;
        $lastMessage = $lobby->messages()->latest('sent_at')->limit(1)->first();
        if ($lastMessage) {
            $lastSequence = $lastMessage->sequence;
        }
        return $lastSequence;
    }

    /**
     * @param string $url
     * @return string|null
     */
    private function makeFullUrl(?string $url)
    {
        if ($url) {
            return rtrim(env('AWS_URL'), '/') . '/' . $url;
        }
        return $url;
    }

    /**
     * @param User $user
     * @param Match $match
     * @return User|null
     */
    private function getOpponent(User $user, Match $match)
    {
        $participants = $match->getParticipants();
        foreach ($participants as $participant) {
            $captain = $participant->getCaptain();
            if ($captain && $user->id != $captain->id) {
                return $captain;
            }
        }
        return null;
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @return User
     */
    private function prepareUserObjectForLobby(User $user, Lobby $lobby)
    {
        $isOrganizer = false;
        $team = $this->getUserTeamInLobby($user, $lobby);
        $user->is_organizer = $isOrganizer;
        $user->team = $team;
        $user->avatar = $this->makeFullUrl($user->avatar);
        $user->cover = $this->makeFullUrl($user->cover);
        if (isset($user->team)) {
            $user->team->logo = $this->makeFullUrl($user->team->logo);
            $user->team->cover = $this->makeFullUrl($user->team->cover);
        }
        return $user;
    }


}
