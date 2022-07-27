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
     * @var Lobby $lobby
     */
    protected $lobby;

    /**
     * @var LobbyRepository $lobbyRepository
     */
    protected $lobbyRepository;

    /**
     * FireUpPrematchPreparation constructor.
     * @param Lobby $lobby
     * @param LobbyRepository $lobbyRepository
     */
    public function __construct(Lobby $lobby, LobbyRepository $lobbyRepository)
    {
        $this->lobby = $lobby;
        $this->lobbyRepository = $lobbyRepository;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->lobbyRepository->createAutoCoinTossMessage($this->lobby);
        sleep(1);
        $this->lobbyRepository->creatPickAndBanFirstMessage($this->lobby);
    }
}
