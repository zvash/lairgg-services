<?php

namespace App\Jobs;

use App\UserLobby;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitUserPresentationInLobbyStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array $message
     */
    protected $message;

    /**
     * SubmitUserPresentationInLobbyStatus constructor.
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = json_decode($message, 1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userId = $this->message['user_id'];
        $lobbyName = $this->message['name'];
        $isPresent = $this->message['is_present'];
        UserLobby::insertOrUpdate($userId, $lobbyName, $isPresent);
    }
}
