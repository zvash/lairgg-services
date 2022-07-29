<?php

namespace App\Console\Commands;

use App\Jobs\FireUpPrematchPreparation;
use App\Jobs\ProcessAutoPickBanTimeoutMessage;
use App\Repositories\LobbyRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SubscribeToRedisFireUpPreMatchPreparationChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prematch:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribes to redis lobby fire up pre match preparation channel';

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
        $redis->subscribe(['lobby-fire-up-pre-match-preparation-channel'], function ($message) {
            $info = json_decode($message, 1);
            return dispatch_now(new FireUpPrematchPreparation($info['lobby_id']));
        });
    }
}
