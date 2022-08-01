<?php

namespace App\Jobs;

use App\Lobby;
use App\Repositories\LobbyRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FireUpPrematchPreparation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int $lobbyId
     */
    protected $lobbyId;

    /**
     * FireUpPrematchPreparation constructor.
     * @param int $lobbyId
     */
    public function __construct(int $lobbyId)
    {
        $this->lobbyId = $lobbyId;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lobby = Lobby::find($this->lobbyId)->load('owner');
        $lobby->owner = $lobby->lobby_aware;
        $lobbyRepository = new LobbyRepository();
        $lobbyRepository->createAutoCoinTossMessage($lobby);
        sleep(1);
        $lobbyRepository->createPickAndBanFirstMessage($lobby);
    }
}
