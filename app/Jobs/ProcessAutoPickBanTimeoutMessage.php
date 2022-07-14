<?php

namespace App\Jobs;

use App\Lobby;
use App\PickBanTimeout;
use App\Repositories\LobbyRepository;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAutoPickBanTimeoutMessage implements ShouldQueue
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
     * @return string
     */
    public function handle()
    {
        try {
            $pickBanTimeout = PickBanTimeout::query()
                ->where('id', $this->message['id'])
                ->where('current_step', $this->message['step'])
                ->first();
            echo json_encode($this->message) . "\n";
            if ($pickBanTimeout && ! $pickBanTimeout->manually_selected) {
                echo "in\n";
                $user = User::find($pickBanTimeout->user_id);
                $lobby = Lobby::query()->where('name', $pickBanTimeout->lobby_name)->first();
                if ($user && $lobby) {
                    echo "user and lobby found\n";
                    if ($pickBanTimeout->action_type == 'side') {
                        echo "side message \n";
                        $mapId = $pickBanTimeout->arguments['map_id'];
                        $mode = $pickBanTimeout->arguments['mode'];
                        $this->lobbyRepository->pickSide($user, $lobby, $mapId, $mode);
                    } else if (in_array($pickBanTimeout->action_type, ['pick', 'ban'])) {
                        echo "{$pickBanTimeout->action_type} message \n";
                        $mapId = $pickBanTimeout->arguments['map_id'];
                        $this->lobbyRepository->pickOrBanMap($user, $lobby, $mapId, $pickBanTimeout->action_type);
                    }
                }
            }
        } catch (\Exception $exception) {
            echo "error: {$exception->getMessage()}";
        }
        return 'done';
    }
}
