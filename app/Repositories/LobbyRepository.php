<?php

namespace App\Repositories;


use App\Dispute;
use App\Enums\DisputeState;
use App\Enums\ParticipantAcceptanceState;
use App\Events\MatchLobbyHadAnAction;
use App\Http\Requests\CreateDisputeRequest;
use App\Lobby;
use App\LobbyMessage;
use App\Match;
use App\MatchParticipant;
use App\Participant;
use App\PickBanTimeout;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

    /**
     * @param Lobby $lobby
     * @return bool
     */
    public function isTournamentLobby(Lobby $lobby)
    {
        return $lobby->owner instanceof Tournament;
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
                    $captain = Team::find($participant->participantable_id)->players()->whereCaptain(1)->first();
                    if ($captain && $captain->user_id == $user->id) {
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }

    public function createReadyMessage(Lobby $lobby, User $user)
    {
        if (!$this->isMatchLobby($lobby)) {
            return null;
        }
        $staffUserId = $this->getFirstStaffUserId($lobby);
        $staff = $this->prepareStaffUserObjectForLobbyByUserId($staffUserId);
        $lobbyMessage = $this->getLobbyMessageWithType($lobby, 'ready_message');
        $match = $lobby->owner;
        if (!$lobbyMessage) {
            $timestamp = time();
            $uuid = Str::orderedUuid()->toString();
            $message = [
                'text' => 'TEAMS STATUS',
                'subtitle' => 'Waiting for your opponent to ready...',
                'type' => 'ready_message',
                'user' => $staff->toArray(),
                'timestamp' => $timestamp,
                'uuid' => $uuid,
                'lobby_name' => $lobby->name,
                'is_final' => false,
                'opponents' => [
                    0 => $this->getParticipantInformation($user, $match),
                ],
            ];
            $lobbyMessageAttributes = [
                'uuid' => $uuid,
                'lobby_id' => $lobby->id,
                'user_id' => $staff->id,
                'lobby_name' => $lobby->name,
                'type' => 'ready_message',
                'sequence' => $this->getNextSequence($lobby),
                'sent_at' => date('Y-m-d H:i:s', $timestamp),
                'message' => json_encode($message),
            ];
            $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
            $lobbyMessage->save();
            Redis::publish('lobby-server-message-channel', json_encode($message));
        } else {
            $message = $this->extractMessageFromLobbyMessage($lobbyMessage);
            $participantInformation = $this->getParticipantInformation($user, $match);
            if (count($message['opponents']) == 1 && $message['opponents'][0]['id'] != $participantInformation['id']) {
                $message['is_final'] = true;
                $message['subtitle'] = null;
                $message['opponents'][] = $participantInformation;
                $lobbyMessage->message = json_encode($message);
                $lobbyMessage->save();
                Redis::publish('lobby-server-edit-message-channel', $lobbyMessage->message);
            }
        }
        return $lobbyMessage->uuid;
    }

    public function createGuidelineMessage(Lobby $lobby)
    {
        if (!$this->isMatchLobby($lobby)) {
            return null;
        }
        $match = $lobby->owner;
        $timestamp = time();
        $lobbyMessage = $this->getLobbyMessageWithType($lobby, 'guideline');
        if ($lobbyMessage) {
            return $lobbyMessage->uuid;
        }
        $staffUserId = $this->getFirstStaffUserId($lobby);
        $staff = $this->prepareStaffUserObjectForLobbyByUserId($staffUserId);
        $uuid = Str::orderedUuid()->toString();

        //============

        $firstParticipantTitle = 'Team A';
        $firstParticipantCaptainUsername = 'CaptainA';
        $secondParticipantTitle = 'Team B';
        $secondParticipantCaptainUsername = 'CaptainB';
        $gameName = 'VALORANT';

        $sections = [];
        $sections[] = [
            'offset' => 1,
            'title' => 'CREATE TEAM',
            'body' => view('lobby.guideline.first', compact('firstParticipantTitle', 'firstParticipantCaptainUsername'))->render(),
        ];
        $sections[] = [
            'offset' => 2,
            'title' => 'INVITE',
            'body' => view('lobby.guideline.second', compact('gameName' ,'firstParticipantTitle', 'firstParticipantCaptainUsername', 'secondParticipantTitle', 'secondParticipantCaptainUsername'))->render(),
        ];
        $sections[] = [
            'offset' => 3,
            'title' => 'START GAME',
            'body' => view('lobby.guideline.third', compact('firstParticipantTitle', 'firstParticipantCaptainUsername', 'secondParticipantTitle', 'secondParticipantCaptainUsername'))->render(),
        ];

        $sections[] = [
            'offset' => 4,
            'title' => 'REPORT RESULTS',
            'body' => view('lobby.guideline.forth', compact('firstParticipantTitle', 'firstParticipantCaptainUsername', 'secondParticipantTitle', 'secondParticipantCaptainUsername'))->render(),
        ];

        //============

        $message = [
            'text' => 'Start your match!',
            'subtitle' => 'Follow these steps to play the game:',
            'type' => 'guideline',
            'user' => $staff->toArray(),
            'timestamp' => $timestamp,
            'uuid' => $uuid,
            'lobby_name' => $lobby->name,
            'is_final' => true,
            'sections' => $sections,
        ];
        $lobbyMessageAttributes = [
            'uuid' => $uuid,
            'lobby_id' => $lobby->id,
            'user_id' => $staff->id,
            'lobby_name' => $lobby->name,
            'type' => 'guideline',
            'sequence' => $this->getNextSequence($lobby),
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
            'message' => json_encode($message),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        Redis::publish('lobby-server-message-channel', json_encode($message));
        return $lobbyMessage->uuid;
    }

    public function creatPickAndBanFirstMessage(Lobby $lobby)
    {
        if (!$this->isMatchLobby($lobby)) {
            return null;
        }
        $match = $lobby->owner;
        $timestamp = time();
        $lobbyMessage = $this->getLobbyMessageWithType($lobby, 'pick_and_ban');
        if ($lobbyMessage) {
            return $lobbyMessage->uuid;
        }
        $newMessage = $this->getPickAndBanActionsAndInformation($match, $lobby, null);
        $newMessage['timestamp'] = $timestamp;
        $uuid = $newMessage['uuid'];
        $participants = $match->getParticipants()->toArray();
        if (count($participants) != 2) {
            return 0;
        }
        $lobbyMessageAttributes = [
            'uuid' => $uuid,
            'lobby_id' => $lobby->id,
            'user_id' => $newMessage['user']['id'],
            'lobby_name' => $lobby->name,
            'type' => 'pick_and_ban',
            'sequence' => $this->getNextSequence($lobby),
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
            'message' => json_encode($newMessage),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        Redis::publish('lobby-server-message-channel', json_encode($newMessage));
        return $lobbyMessage->uuid;
    }

    public function createBigTitleMessage(Lobby $lobby, string $title)
    {
        if (!$this->isMatchLobby($lobby)) {
            return null;
        }
        $match = $lobby->owner;
        $timestamp = time();
        $uuid = Str::orderedUuid()->toString();
        $staffUserId = $this->getFirstStaffUserId($lobby);
        $staff = $this->prepareStaffUserObjectForLobbyByUserId($staffUserId);
        $newMessage = [
            'type' => 'big_title',
            'user' => $staff->toArray(),
            'timestamp' => $timestamp,
            'text' => $title,
            'uuid' => $uuid,
            'lobby_name' => $lobby->name,
            'is_final' => true,
        ];
        $lobbyMessageAttributes = [
            'uuid' => $uuid,
            'lobby_id' => $lobby->id,
            'user_id' => $staff->id,
            'lobby_name' => $lobby->name,
            'type' => 'big_title',
            'sequence' => $this->getNextSequence($lobby),
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
            'message' => json_encode($newMessage),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        Redis::publish('lobby-server-message-channel', json_encode($newMessage));
        return $lobbyMessage->uuid;
    }

    public function createAutoCoinTossMessage(Lobby $lobby)
    {
        if (!$this->isMatchLobby($lobby)) {
            return null;
        }
        $match = $lobby->owner;
        $timestamp = time();
        $uuid = Str::orderedUuid()->toString();
        $staffUserId = $this->getFirstStaffUserId($lobby);
        $staff = $this->prepareStaffUserObjectForLobbyByUserId($staffUserId);
        $lobbyMessage = $this->getLobbyMessageWithType($lobby, 'auto_coin_toss');
        if ($lobbyMessage) {
            return $lobbyMessage->uuid;
        }
        $participants = $match->getParticipants()->toArray();
        if (count($participants) != 2) {
            return 0;
        }
        $winnerIndex = mt_rand(0, 1);
        $winner = $participants[$winnerIndex];
        $match->coin_toss_winner_id = $winner['id'];
        $match->save();
        $coinTossResult = $this->getAutoCoinTossInformation($match);
        $newMessage = [
            'type' => 'auto_coin_toss',
            'user' => $staff->toArray(),
            'timestamp' => $timestamp,
            'text' => $coinTossResult['title'],
            'result' => $coinTossResult['text'],
            'uuid' => $uuid,
            'lobby_name' => $lobby->name,
            'is_final' => true,
            'winner' => null,
            'loser' => null,
        ];
        $lobbyMessageAttributes = [
            'uuid' => $uuid,
            'lobby_id' => $lobby->id,
            'user_id' => $staff->id,
            'lobby_name' => $lobby->name,
            'type' => 'auto_coin_toss',
            'sequence' => $this->getNextSequence($lobby),
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
            'message' => json_encode($newMessage),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        Redis::publish('lobby-server-message-channel', json_encode($newMessage));
        return $lobbyMessage->uuid;
    }

    public function creatCoinTossMessage(User $user, Lobby $lobby, string $title)
    {
        if (!$this->isMatchLobby($lobby)) {
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
                ], [
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
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
            'message' => json_encode($newMessage),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        Redis::publish('lobby-server-message-channel', json_encode($newMessage));
        event(new MatchLobbyHadAnAction($match, $user, 'coin_toss_request'));
        return $lobbyMessage->uuid;
    }

    /**
     * @param Lobby $lobby
     * @param string $uuid
     * @return array|null
     */
    public function loadPreviousMessages(Lobby $lobby, string $uuid)
    {
        $message = $lobby->messages()->whereUuid($uuid)->first();
        if (!$message) {
            return null;
        }
        return $this->getMessages($lobby, true, $message->sequence);
    }

    /**
     * @param Lobby $lobby
     * @param string $uuid
     * @return array|null
     */
    public function loadNextMessages(Lobby $lobby, string $uuid)
    {
        $message = $lobby->messages()->whereUuid($uuid)->first();
        if (!$message) {
            return null;
        }
        return $this->getMessages($lobby, false, $message->sequence, 0);
    }

    /**
     * @param Lobby $lobby
     * @param bool $backward
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getMessages(Lobby $lobby, bool $backward = true, $from = 0, int $limit = 20)
    {

//        $query = $lobby->messages()->latest('sequence');

        if ($from) {
            $message = $lobby->messages()->where('sequence', $from)->first();
            $query = $lobby->messages()->latest('sent_at')->where('id', '<>', $message->id);
            if ($backward) {
//                $query = $query->where('sequence', '<', $from);
                $query = $query->where('sent_at', '<=', $message->sent_at);

            } else {
//                $query = $query->where('sequence', '>', $from);
                $query = $query->where('sent_at', '>=', $message->sent_at);
            }
        } else {
            $query = $lobby->messages()->latest('sent_at');
        }
        if ($limit) {
            $lobbyMessages = $query->limit($limit)->get();
        } else {
            $lobbyMessages = $query->get();
        }
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
                $message['user']['avatar'] = rtrim(config('aws.cloud_front_url'), '/') . '/' . $usersById[$lobbyMessage->user_id]->avatar;
            }

            if (isset($message['user']['team'])) {
                $message['user']['team']['title'] = $teamsById[$message['user']['team']['id']]->title;
                if ($teamsById[$message['user']['team']['id']]->logo) {
                    $message['user']['team']['logo'] = rtrim(config('aws.cloud_front_url'), '/') . '/' . $teamsById[$message['user']['team']['id']]->logo;
                }
                if ($teamsById[$message['user']['team']['id']]->cover) {
                    $message['user']['team']['cover'] = rtrim(config('aws.cloud_front_url'), '/') . '/' . $teamsById[$message['user']['team']['id']]->cover;
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
        $key = config('database.redis.options.prefix') . $lobby->name . '-sequence';
        return Redis::eval(file_get_contents($script), 1, $key, $lastSequence);
    }

    /**
     * @param Lobby $lobby
     */
    public function resetLastSequenceForLobby(Lobby $lobby)
    {
        $lastSequence = $this->getLastSequenceForLobby($lobby);
        $key = config('database.redis.options.prefix') . $lobby->name . '-sequence';
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
                event(new MatchLobbyHadAnAction($match, $user, 'coin_toss_accepted'));
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
            $rejecterTeam = $this->getUserTeamInLobby($user, $lobby);
            $message = json_decode($lobbyMessage->message, 1);
            if ($message['actions_are_visible_to'] == $user->id && $message['status'] == 'pending') {
                $message['status'] = 'rejected';
                $message['rejecter'] = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                ];
                if ($rejecterTeam) {
                    $message['rejecter']['team_id'] = $rejecterTeam->id;
                    $message['rejecter']['team_title'] = $rejecterTeam->title;
                }
                $message['is_final'] = true;
                $message['actions'] = [];
                $message['actions_are_visible_to'] = 0;
                $lobbyMessage->message = json_encode($message);
                $lobbyMessage->save();
                Redis::publish('lobby-server-edit-message-channel', $lobbyMessage->message);
                event(new MatchLobbyHadAnAction($lobby->owner, $user, 'coin_toss_declined'));
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
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
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
        event(new MatchLobbyHadAnAction($lobby->owner, $request->user(), 'dispute_submitted'));
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
                    if ($player->pivot->captain) {
                        $participantsUserIds[] = $player->user_id;
                    }
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
            return rtrim(config('aws.cloud_front_url'), '/') . '/' . $url;
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
     * @param Match $match
     * @return array|null
     */
    private function getParticipantInformation(User $user, Match $match)
    {
        $participants = $match->getParticipants();
        foreach ($participants as $participant) {
            $captain = $participant->getCaptain();
            if ($captain && $captain->id == $user->id) {
                $matchParticipant = MatchParticipant::query()
                    ->where('participant_id', $participant->id)
                    ->where('match_id', $match->id)
                    ->first();
                $readyAt = null;
                $readyAtWithTimeZone = null;
                if ($matchParticipant && $matchParticipant->ready_at) {
                    $readyAt = $matchParticipant->ready_at;
                    $readyAtWithTimeZone = $matchParticipant->ready_at_with_timezone;
                }
                if ($participant->participantable_type == Team::class) {
                    $team = Team::find($participant->participantable_id);
                    return [
                        'id' => $participant->id,
                        'title' => 'Team ' . $team->title . ' is ready!',
                        'logo' => $this->makeFullUrl($team->logo),
                        'ready_at' => $readyAtWithTimeZone,
                    ];

                } else if ($participant->participantable_type == User::class) {
                    return [
                        'id' => $participant->id,
                        'title' => $captain->username . ' is ready!',
                        'logo' => $this->makeFullUrl($captain->avatar),
                        'ready_at' => $readyAtWithTimeZone,
                    ];
                }
            }
        }
        return null;
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @param int $mapId
     * @param string $action
     * @return string
     * @throws \Exception
     */
    public function pickOrBanMap(User $user, Lobby $lobby, int $mapId, string $action)
    {
        $match = $lobby->owner;
        $lobbyMessage = $this->getLobbyMessageWithType($lobby, 'pick_and_ban');
        if (!$lobbyMessage) {
            throw new \Exception(__('strings.operation_cannot_be_done'));
        }
        $lastMessage = $this->extractMessageFromLobbyMessage($lobbyMessage);
        $participantInformationByTurn = $this->getParticipantsInformationByTurn($match);
        if (
            $lastMessage['actions']
            && array_key_exists('type', $lastMessage['actions'])
            && $lastMessage['actions']['type'] == $action
            && array_key_exists('visible_to', $lastMessage['actions'])
            && $lastMessage['actions']['visible_to'] == $user->id
            && array_key_exists('maps', $lastMessage)
            && is_array($lastMessage['maps'])
        ) {
            $activeParticipant = null;
            foreach ($participantInformationByTurn as $item) {
                if ($item['captain_id'] == $user->id) {
                    $activeParticipant = $item;
                    break;
                }
            }
            if (!$activeParticipant) {
                throw new \Exception(__('strings.invalid_request'));
            }

            $lastMessage['image'] = $activeParticipant['logo'];
            $prefix = '';
            if ($activeParticipant['type'] == 'team') {
                $prefix = 'Team ';
            }

            $actionText = 'picked';
            if ($action == 'ban') {
                $actionText = 'banned';
            }

            $mapsInformation = $lastMessage['maps'];
            $mapTitle = '';
            $choosableMapId = 0;
            foreach ($mapsInformation as $index => $information) {
                if ($information['map_id'] == $mapId && $information['status'] == 'undecided') {
                    $mapTitle = $information['title'];
                    $choosableMapId = $information['map_id'];
                    $mapsInformation[$index]['status'] = $actionText;
                    $lastMessage['maps'] = $mapsInformation;
                    break;
                }
            }
            if ($mapId != $choosableMapId) {
                throw new \Exception(__('strings.invalid_request'));
            }

            $lastMessage['summary'][] = [
                'text' => "{$prefix}{$activeParticipant['title']} {$actionText} {$mapTitle}",
                'image' => $activeParticipant['logo'],
                'timestamp' => time(),
            ];
            $lobbyMessage->message = json_encode($lastMessage);
            $lobbyMessage->save();

            $lastMessage = $this->getPickAndBanActionsAndInformation($match, $lobby, $lastMessage);

            $lobbyMessage->message = json_encode($lastMessage);
            $lobbyMessage->save();
            Redis::publish('lobby-server-edit-message-channel', $lobbyMessage->message);
            return $actionText;
        }
        throw new \Exception(__('strings.operation_cannot_be_done'));
    }

    /**
     * @param User $user
     * @param Lobby $lobby
     * @param int $mapId
     * @param string $mode
     * @return string
     * @throws \Exception
     */
    public function pickSide(User $user, Lobby $lobby, int $mapId, string $mode)
    {
        $match = $lobby->owner;
        $lobbyMessage = $this->getLobbyMessageWithType($lobby, 'pick_and_ban');
        if (!$lobbyMessage) {
            throw new \Exception(__('strings.operation_cannot_be_done'));
        }
        $lastMessage = $this->extractMessageFromLobbyMessage($lobbyMessage);
        $participantInformationByTurn = $this->getParticipantsInformationByTurn($match);
        if (
            $lastMessage['actions']
            && array_key_exists('type', $lastMessage['actions'])
            && $lastMessage['actions']['type'] == 'side'
            && array_key_exists('visible_to', $lastMessage['actions'])
            && $lastMessage['actions']['visible_to'] == $user->id
            && array_key_exists('maps', $lastMessage)
            && is_array($lastMessage['maps'])
        ) {
            $mapsInformation = $lastMessage['maps'];
            $activeParticipant = null;
            $mapTitle = '';
            $choosableMapId = 0;
            foreach ($mapsInformation as $index => $information) {
                if ($information['status'] == 'picked' && $information['attacker'] === null && $information['defender'] === null) {
                    $mapTitle = $information['title'];
                    $choosableMapId = $information['map_id'];
                    if ($choosableMapId != $mapId) {
                        throw new \Exception(__('strings.invalid_request'));
                    }
                    $otherMode = 'defender';
                    if ($mode == 'defender') {
                        $otherMode = 'attacker';
                    }
                    $activeParticipant = [];
                    $otherParticipant = [];
                    foreach ($participantInformationByTurn as $item) {
                        if ($item['captain_id'] == $user->id) {
                            $activeParticipant = $item;
                        } else {
                            $otherParticipant = $item;
                        }
                    }
                    if (!$activeParticipant) {
                        throw new \Exception(__('strings.invalid_request'));
                    }

                    $mapsInformation[$index][$mode] = [
                        'title' => $activeParticipant['title'],
                        'logo' => $activeParticipant['logo'],
                    ];
                    $mapsInformation[$index][$otherMode] = [
                        'title' => $otherParticipant['title'],
                        'logo' => $otherParticipant['logo'],
                    ];
                    $lastMessage['maps'] = $mapsInformation;

                    $lastMessage['image'] = $activeParticipant['logo'];
                    $prefix = '';
                    if ($activeParticipant['type'] == 'team') {
                        $prefix = 'Team ';
                    }

                    $capitalizedMode = ucfirst($mode);
                    $lastMessage['summary'][] = [
                        'text' => "{$prefix}{$activeParticipant['title']} picked side {$capitalizedMode} for {$mapTitle}",
                        'image' => $activeParticipant['logo'],
                        'timestamp' => time(),
                    ];
                    $lobbyMessage->message = json_encode($lastMessage);
                    $lobbyMessage->save();

                    $lastMessage = $this->getPickAndBanActionsAndInformation($match, $lobby, $lastMessage);

                    $lobbyMessage->message = json_encode($lastMessage);
                    $lobbyMessage->save();
                    Redis::publish('lobby-server-edit-message-channel', $lobbyMessage->message);
                    if ($lastMessage['is_final']) {
                        sleep(1);
                        $this->createGuidelineMessage($lobby);
                    }
                    return 'done';
                }
            }
        }
        throw new \Exception(__('strings.operation_cannot_be_done'));
    }

    /**
     * @param Match $match
     * @param Lobby $lobby
     * @param array|null $lastMessage
     * @return array
     */
    private function getPickAndBanActionsAndInformation(Match $match, Lobby $lobby, array $lastMessage = null)
    {
        $playCount = $match->plays()->count();
        $participantInformationByTurn = $this->getParticipantsInformationByTurn($match);
        if (!$lastMessage) {
            $maps = $match->tournament->game->maps;
            $mapsInformation = [];
            foreach ($maps as $map) {
                $mapsInformation[] = [
                    'map_id' => $map->id,
                    'title' => $map->title,
                    'image' => $this->makeFullUrl($map->image),
                    'status' => 'undecided',
                    'defender' => null,
                    'attacker' => null,
                ];
            }
            $currentStep = 0;
            $staffUserId = $this->getFirstStaffUserId($lobby);
            $staff = $this->prepareStaffUserObjectForLobbyByUserId($staffUserId);
            $lastMessage = [
                'type' => 'pick_and_ban',
                'user' => $staff->toArray(),
                'timestamp' => time(),
                'text' => '',
                'image' => null,
                'uuid' => Str::orderedUuid()->toString(),
                'lobby_name' => $lobby->name,
                'is_final' => false,
                'current_step' => $currentStep,
                'summary' => [],
                'maps' => $mapsInformation,
                'actions' => [],
            ];

        } else {
            $currentStep = $lastMessage['current_step'] + 1;
            $lastMessage['current_step'] = $currentStep;
            $mapsInformation = $lastMessage['maps'];
        }
        $allSteps = $this->getPackAndBanActionByStepForValorant($playCount, count($mapsInformation));
        if ($currentStep == $allSteps['total_steps'] - 1) {
            $lastMessage['text'] = 'SUMMARY';
            $lastMessage['image'] = null;
            $lastMessage['is_final'] = true;
            $lastMessage['actions'] = [];
            $this->resetPickBanTimeout($lobby, $lastMessage);
            return $lastMessage;
        }
        $currentAction = $allSteps['actions'][$currentStep];
        if ($currentAction['auto']) {
            foreach ($mapsInformation as $index => $information) {
                if ($information['status'] == 'undecided') {
                    $mapsInformation[$index]['status'] = 'picked';
                    $lastMessage['maps'] = $mapsInformation;
                    $lastMessage['summary'][] = [
                        'text' => "{$information['title']} is only map remaining.",
                        'image' => null,
                        'timestamp' => time(),
                    ];
                    $currentStep++;
                    if ($currentStep == $allSteps['total_steps']) {
                        $lastMessage['text'] = 'SUMMARY';
                        $lastMessage['image'] = null;
                        $lastMessage['is_final'] = true;
                        $lastMessage['actions'] = [];
                        $this->resetPickBanTimeout($lobby, $lastMessage);
                        return $lastMessage;
                    }
                    $currentAction = $allSteps['actions'][$currentStep];
                    break;
                }
            }
        }
        $activeParticipant = $participantInformationByTurn[$currentAction['turn']];
        $lastMessage['image'] = $activeParticipant['logo'];
        $prefix = '';
        if ($activeParticipant['type'] == 'team') {
            $prefix = 'Team ';
        }
        if ($currentAction['action'] == 'ban') {
            $lastMessage['text'] = $prefix . $activeParticipant['title'] . '\'s turn to ban a map.';
            $lastMessage['actions'] = [
                'type' => 'ban',
                'visible_to' => $activeParticipant['captain_id'],
                'deadline' => time() + 60,
                'url_count' => 1,
                'url_prefixes' => ["api/v1/lobbies/{$lobby->name}/picknban/ban/maps/"],
            ];
        } else if ($currentAction['action'] == 'pick') {
            $lastMessage['text'] = $prefix . $activeParticipant['title'] . '\'s turn to pick a map.';
            $lastMessage['actions'] = [
                'type' => 'pick',
                'visible_to' => $activeParticipant['captain_id'],
                'deadline' => time() + 60,
                'url_count' => 1,
                'url_prefixes' => ["api/v1/lobbies/{$lobby->name}/picknban/pick/maps/"],
            ];
        } else if ($currentAction['action'] == 'side') {
            $mapTitle = '';
            $mapId = 0;
            foreach ($mapsInformation as $information) {
                if ($information['status'] == 'picked' && $information['attacker'] === null && $information['defender'] === null) {
                    $mapTitle = $information['title'];
                    $mapId = $information['map_id'];
                    break;
                }
            }
            $lastMessage['text'] = $prefix . $activeParticipant['title'] . '\'s turn to pick a side for map **' . $mapTitle . '**';
            $lastMessage['actions'] = [
                'type' => 'side',
                'visible_to' => $activeParticipant['captain_id'],
                'deadline' => time() + 60,
                'url_count' => 2,
                'urls' => [
                    'attacker' => "api/v1/lobbies/{$lobby->name}/picknban/side/maps/$mapId/attacker",
                    'defender' => "api/v1/lobbies/{$lobby->name}/picknban/side/maps/$mapId/defender",
                ]
            ];
        }
        $this->resetPickBanTimeout($lobby, $lastMessage);
        return $lastMessage;
    }

    /**
     * @param Lobby $lobby
     * @param array $message
     * @return PickBanTimeout|null
     */
    private function resetPickBanTimeout(Lobby $lobby, array $message)
    {
        $lobbyName = $lobby->name;
        if ($message['is_final']) {
            PickBanTimeout::query()
                ->where('lobby_name', $lobbyName)
                ->delete();
            return null;
        }
        $pickBanTimeout = PickBanTimeout::query()
            ->where('lobby_name', $lobbyName)
            ->first();
        if (!$pickBanTimeout) {
            $pickBanTimeout = new PickBanTimeout();
            $pickBanTimeout->lobby_name = $lobbyName;
        }
        $actions = $message['actions'];
        $pickBanTimeout->action_type = $actions['type'];
        $pickBanTimeout->current_step = $message['current_step'];
        $pickBanTimeout->user_id = $actions['visible_to'];
        $pickBanTimeout->manually_selected = false;
        $pickBanTimeout->deadline = $actions['deadline'];

        $maps = $message['maps'];
        if ($actions['type'] == 'side') {
            foreach ($maps as $map) {
                if ($map['status'] == 'picked' && $map['attacker'] === null && $map['defender'] === null) {
                    $pickBanTimeout->arguments = [
                        'map_id' => $map['map_id'],
                        'mode' => ['attacker', 'defender'][mt_rand(0, 1)],
                    ];
                    break;
                }
            }
        } else {
            $undecidedMaps = [];
            foreach ($maps as $map) {
                if ($map['status'] == 'undecided') {
                    $undecidedMaps[] = $map['map_id'];
                }
            }
            if ($undecidedMaps) {
                $pickBanTimeout->arguments = [
                    'map_id' => $undecidedMaps[mt_rand(0, count($undecidedMaps) - 1)],
                ];
            }
        }
        $pickBanTimeout->save();
        $pickBanTimeout->refresh();
        $payload = [
            'id' => $pickBanTimeout->id,
            'step' => $pickBanTimeout->current_step,
            'deadline' => $pickBanTimeout->deadline,
        ];
        Redis::publish('lobby-server-pick-and-ban-timeout-channel', json_encode($payload));
        return $pickBanTimeout;
    }

    /**
     * @param Match $match
     * @return array
     */
    private function getMatchParticipantsById(Match $match)
    {
        $participants = $match->getParticipants()->toArray();
        $participantsById = [];
        foreach ($participants as $participant) {
            $participantsById[$participant['id']] = $participant;
        }
        return $participantsById;
    }

    private function getAutoCoinTossInformation(Match $match)
    {
        $playCount = $match->plays()->count();
        $title = "PICK & BAN (BO{$playCount})";
        $participant = Participant::find($match->coin_toss_winner_id);
        $text = '';
        if ($participant->participantable_type == Team::class) {
            $team = Team::find($participant->participantable_id);
            $text = "Team {$team->title} won the coin toss. They will choose to ban the first map.";
        } else if ($participant->participantable_type == User::class) {
            $captain = $participant->getCaptain();
            $text = "{$captain->username} won the coin toss. They will choose to ban the first map.";
        }
        return [
            'title' => $title,
            'text' => $text,
        ];
    }

    /**
     * @param int $participantId
     * @return array
     */
    private function getParticipantInformationById(int $participantId)
    {
        $participant = Participant::find($participantId);
        $captainId = $participant->getCaptain()->id;
        if ($participant->participantable_type == Team::class) {
            $team = Team::find($participant->participantable_id);
            return [
                'participant_id' => $participantId,
                'participantable_id' => $participant->participantable_id,
                'type' => 'team',
                'title' => $team->title,
                'logo' => $this->makeFullUrl($team->logo),
                'captain_id' => $captainId,
            ];
        } else {
            $user = User::find($participant->participantable_id);
            return [
                'participant_id' => $participantId,
                'participantable_id' => $participant->participantable_id,
                'type' => 'user',
                'title' => $user->username,
                'logo' => $this->makeFullUrl($user->avatar),
                'captain_id' => $captainId,
            ];
        }
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

    /**
     * @param int $userId
     * @return mixed
     */
    private function prepareStaffUserObjectForLobbyByUserId(int $userId)
    {
        $user = User::find($userId);
        $user->is_organizer = true;
        $user->is_auto = true;
        $user->avatar = $this->makeFullUrl($user->avatar);
        $user->cover = $this->makeFullUrl($user->cover);
        return $user;
    }

    /**
     * @param Lobby $lobby
     * @return int
     */
    private function getFirstStaffUserId(Lobby $lobby)
    {
        $organization = null;
        if ($this->isMatchLobby($lobby)) {
            $match = Match::find($lobby->lobby_aware_id);
            $organization = $match->tournament->organization;
            return $organization->staff()->first()->user_id;
        } else if ($this->isTournamentLobby($lobby)) {
            $tournament = Tournament::find($lobby->lobby_aware_id);
            $organization = $tournament->organization;
            return $organization->staff()->first()->user_id;
        }
        return 1;
    }

    private function getLobbyMessageWithType(Lobby $lobby, string $type)
    {
        return LobbyMessage::query()
            ->where('lobby_id', $lobby->id)
            ->where('type', $type)
            ->first();
    }

    private function extractMessageFromLobbyMessage(LobbyMessage $lobbyMessage)
    {
        return json_decode($lobbyMessage->message, true);
    }

    /**
     * @param int $playCount
     * @param int $mapCount
     * @return mixed
     */
    public function getPackAndBanActionByStepForValorant(int $playCount, int $mapCount)
    {
        $steps = [
            1 => [],
            3 => [],
            5 => [],
        ];
        //BO1
        $steps[1]['total_steps'] = $mapCount + 1;
        $steps[1]['actions'] = [];
        for ($i = 0; $i < $steps[1]['total_steps']; $i++) {
            $turn = $i % 2;
            if ($i == $steps[1]['total_steps'] - 1) {//last step of bo1
                $steps[1]['actions'][$i] = [
                    'turn' => $steps[1]['actions'][$i - 1]['turn'],
                    'action' => 'side',
                    'deadline' => 60,
                    'auto' => false,
                ];
            } else if ($i == $steps[1]['total_steps'] - 2) {//auto pick of bo1
                $steps[1]['actions'][$i] = [
                    'turn' => $turn,
                    'action' => 'pick',
                    'deadline' => 0,
                    'auto' => true,
                ];
            } else {//bans
                $steps[1]['actions'][$i] = [
                    'turn' => $turn,
                    'action' => 'ban',
                    'deadline' => 60,
                    'auto' => false,
                ];
            }
        }
        // end of BO1

        //BO3
        $pickedMaps = 0;
        $remainedMaps = $mapCount;
        $turn = 0;
        $step = 0;
        $remainedBans = 2;
        $remainedPicks = 2;
        while ($remainedMaps) {
            if ($remainedMaps == 1) {
                $remainedMaps = 0;
                if ($step > 0) {
                    $lastTurn = $steps[3]['actions'][$step - 1]['turn'];
                    if ($steps[3]['actions'][$step - 1]['action'] == 'side') {
                        $turn = $lastTurn;
                    } else {
                        $turn = ($lastTurn + 1) % 2;
                    }
                }
                while ($pickedMaps < $playCount) {
                    $steps[3]['actions'][$step] = [
                        'turn' => $turn,
                        'action' => 'pick',
                        'deadline' => 0,
                        'auto' => true,
                    ];
                    $pickedMaps++;
                    $step++;
                    $steps[3]['actions'][$step] = [
                        'turn' => $turn,
                        'action' => 'side',
                        'deadline' => 60,
                        'auto' => false,
                    ];
                    $turn = ($turn + 1) % 2;
                    $step++;
                }
            } else if ($remainedBans > 0) {
                $turn = $remainedBans % 2;
                $steps[3]['actions'][$step] = [
                    'turn' => $turn,
                    'action' => 'ban',
                    'deadline' => 60,
                    'auto' => false,
                ];
                $step++;
                $remainedBans--;
                $remainedMaps--;
                $remainedPicks = 2;
            } else if ($remainedPicks > 0) {
                $turn = $remainedPicks % 2;
                $steps[3]['actions'][$step] = [
                    'turn' => $turn,
                    'action' => 'pick',
                    'deadline' => 60,
                    'auto' => false,
                ];
                $step++;
                $remainedMaps--;
                $remainedPicks--;
                $pickedMaps++;
                $turn = ($turn + 1) % 2;
                $steps[3]['actions'][$step] = [
                    'turn' => $turn,
                    'action' => 'side',
                    'deadline' => 60,
                    'auto' => false,
                ];
                $step++;
                $turn = ($turn + 1) % 2;
                if ($remainedPicks == 0) {
                    $remainedBans = 2;
                }
            }
        }
        $steps[3]['total_steps'] = $step;
        // end of BO3

        //BO5
        $pickedMaps = 0;
        $remainedMaps = $mapCount;
        $turn = 0;
        $step = 0;
        $remainedBans = $mapCount - $playCount;
        $remainedPicks = $playCount;
        while ($remainedMaps) {
            if ($remainedMaps == 1) {
                $remainedMaps = 0;
                if ($step > 0) {
                    $lastTurn = $steps[5]['actions'][$step - 1]['turn'];
                    if ($steps[5]['actions'][$step - 1]['action'] == 'side') {
                        $turn = $lastTurn;
                    } else {
                        $turn = ($lastTurn + 1) % 2;
                    }
                }
                while ($pickedMaps < $playCount) {
                    $steps[5]['actions'][$step] = [
                        'turn' => $turn,
                        'action' => 'pick',
                        'deadline' => 0,
                        'auto' => true,
                    ];
                    $remainedPicks--;
                    $pickedMaps++;
                    $step++;
                    $steps[5]['actions'][$step] = [
                        'turn' => $turn,
                        'action' => 'side',
                        'deadline' => 60,
                        'auto' => false,
                    ];
                    $turn = ($turn + 1) % 2;
                    $step++;
                }
            } else if ($remainedBans > 0) {
                $steps[5]['actions'][$step] = [
                    'turn' => $turn,
                    'action' => 'ban',
                    'deadline' => 60,
                    'auto' => false,
                ];
                $step++;
                $turn = ($turn + 1) % 2;
                $remainedBans--;
                $remainedMaps--;
            } else if ($remainedPicks > 0) {
                $steps[5]['actions'][$step] = [
                    'turn' => $turn,
                    'action' => 'pick',
                    'deadline' => 60,
                    'auto' => false,
                ];
                $step++;
                $remainedMaps--;
                $remainedPicks--;
                $pickedMaps++;
                $turn = ($turn + 1) % 2;
                $steps[5]['actions'][$step] = [
                    'turn' => $turn,
                    'action' => 'side',
                    'deadline' => 60,
                    'auto' => false,
                ];
                $step++;
            }
        }
        $steps[5]['total_steps'] = $step;
        //end of BO5
        return $steps[$playCount];
    }

    /**
     * @param Match $match
     * @return array
     */
    private function getParticipantsInformationByTurn(Match $match): array
    {
        $participantsById = $this->getMatchParticipantsById($match);
        $turnTable = [];
        foreach ($participantsById as $participantId => $participant) {
            if ($participantId == $match->coin_toss_winner_id) {
                array_unshift($turnTable, $participantId);
            } else {
                $turnTable[] = $participantId;
            }
        }
        $participantInformationByTurn = [];
        foreach ($turnTable as $participantId) {
            $participantInformationByTurn[] = $this->getParticipantInformationById($participantId);
        }
        return $participantInformationByTurn;
    }
}
