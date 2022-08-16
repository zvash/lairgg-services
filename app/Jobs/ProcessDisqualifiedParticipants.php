<?php

namespace App\Jobs;

use App\MatchParticipant;
use App\Repositories\LobbyRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDisqualifiedParticipants
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __invoke()
    {
        $now = Carbon::now();
        $nextMinute = $now->copy()->addMinute()->addSecond();
        $matchParticipants = MatchParticipant::query()
            ->whereNull('disqualified_at')
            ->whereNull('ready_at')
            ->whereRaw("disqualify_deadline >= '{$now->format('Y-m-d H:i:s')}'")
            ->whereRaw("disqualify_deadline < '{$nextMinute->format('Y-m-d H:i:s')}'")
            ->get();
        $lobbyRepository = new LobbyRepository();
        foreach ($matchParticipants as $matchParticipant) {
            dispatch_now(new DisqualifyParticipant($matchParticipant, $lobbyRepository));
        }
    }
}
