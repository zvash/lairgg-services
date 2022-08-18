<?php


namespace App\ScheduledJobs;


use App\Jobs\NotifyAboutMatchStateChanges;
use App\Match;
use Carbon\Carbon;

class NotifyMatchWillNeedAttentionInFuture
{
    public function __invoke()
    {
        $oneHourFromNow = Carbon::now()->addHour();
        $oneMinuteAfterOneHourFromNow = $oneHourFromNow->copy()->addMinute()->subSecond();
        $matchesInOneHour = $this->getFutureMatches($oneHourFromNow, $oneMinuteAfterOneHourFromNow);

        $now = Carbon::now();
        $oneMinuteFromNow = Carbon::now()->addMinute()->subSecond();
        $justStartedMatches = $this->getFutureMatches($now, $oneMinuteFromNow);

        dispatch(new NotifyAboutMatchStateChanges($matchesInOneHour, 'heads_up_1'));
        dispatch(new NotifyAboutMatchStateChanges($justStartedMatches, 'heads_up_pre_match'));
    }


    /**
     * @param Carbon $start
     * @param Carbon $end
     * @return Match[]
     */
    private function getFutureMatches(Carbon $start, Carbon $end)
    {
        return Match::query()
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->get()
            ->all();
    }
}
