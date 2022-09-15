<?php

namespace App\Jobs;

use App\Events\AdminHasBeenMentioned;
use App\Events\LobbyHasANewMessage;
use App\Lobby;
use App\LobbyMessage;
use App\Match;
use App\Repositories\LobbyRepository;
use App\UserLobby;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PersistLobbyMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var LobbyRepository $lobbyRepository
     */
    protected $lobbyRepository;

    /**
     * @var array $message
     */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->lobbyRepository = new LobbyRepository();
        $this->message = json_decode($message, 1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lobby = Lobby::query()->whereName($this->message['lobby_name'])->first();
        if (! $lobby) {
            return;
        }
        $lobbyMessageAttributes = [
            'uuid' => $this->message['uuid'],
            'lobby_id' => $lobby->id,
            'user_id' => $this->message['user']['id'],
            'lobby_name' => $lobby->name,
            'type' => $this->message['type'],
            'sequence' => $this->lobbyRepository->getNextSequence($lobby),
            'sent_at' => date('Y-m-d H:i:s', intval($this->message['timestamp'])),
            'message' => json_encode($this->message),
        ];
        $lobbyMessage = new LobbyMessage($lobbyMessageAttributes);
        $lobbyMessage->save();
        $this->notifyOtherParticipantIfNeeded($this->message['user']['id'], $lobby);
        $this->notifyAdminsIfNeeded($this->message['user']['id'], $lobby);
        return;
    }

    private function notifyOtherParticipantIfNeeded(int $senderId, Lobby $lobby)
    {
        if (!$lobby->owner instanceof Match) {
            return;
        }
        $match = $lobby->owner;
        $allParticipants = $match->getParticipants();
        $participantIds = [];
        foreach ($allParticipants as $participant) {
            $captain = $participant->getCaptain();
            if ($captain->id != $senderId && UserLobby::needsToBeNotified($captain->id, $lobby->name)) {
                $participantIds[] = $participant->id;
            }
        }
        if ($participantIds) {
            event(new LobbyHasANewMessage($match, $senderId, $participantIds));
        }
    }

    /**
     * @param int $senderId
     * @param Lobby $lobby
     */
    private function notifyAdminsIfNeeded(int $senderId, Lobby $lobby)
    {
        if ($this->message['type'] != 'text') {
            return;
        }
        if (!$lobby->owner instanceof Match) {
            return;
        }
        $text = strtolower($this->message['text']);
        if (! preg_match('/[^0-9a-z]@admin[s]?\b/', $text)) {
            return;
        }
        $match = $lobby->owner;
        $organization = $match->tournament->organization;
        $staffUserIds = $organization->staff->pluck('user_id')->all();
        $toBeNotifiedStaffUserIds = [];
        foreach ($staffUserIds as $userId) {
            if ($userId != $senderId && UserLobby::needsToBeNotified($userId, $lobby->name)) {
                $toBeNotifiedStaffUserIds[] = $userId;
            }
        }
        if ($staffUserIds) {
            event(new AdminHasBeenMentioned($match, $senderId, $staffUserIds));
        }
    }
}
