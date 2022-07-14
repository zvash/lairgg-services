<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAutoPickBanTimeoutMessage;
use App\Repositories\LobbyRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SubscribeToRedisLobbyPickAndBanMessageChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pickban:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribes to redis lobby pick and ban channel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $redis = Redis::connection('subscribe');
        $redis->subscribe(['lobby-pick-and-ban-message-channel'], function ($message) {
            return dispatch_now(new ProcessAutoPickBanTimeoutMessage($message));
        });
    }
}
