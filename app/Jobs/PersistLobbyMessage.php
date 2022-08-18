<?php

namespace App\Jobs;

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
}
