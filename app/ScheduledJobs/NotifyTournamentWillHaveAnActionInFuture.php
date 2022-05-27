<?php


namespace App\ScheduledJobs;


use App\Jobs\NotifyAboutTournamentStateChanges;
use App\Tournament;
use Carbon\Carbon;

class NotifyTournamentWillHaveAnActionInFuture
{
    public function __invoke()
    {
        $twentyFourHoursFromNow = Carbon::now()->addDay();
        $twentyFourHoursAndOneMinuteFromNow = Carbon::now()->addDay()->addMinute()->subSecond();
        $tournamentStartingIn24 = $this->getFutureTournaments($twentyFourHoursFromNow, $twentyFourHoursAndOneMinuteFromNow);

        $nextHour = Carbon::now()->addHour();
        $nextHourAnOneMinute = Carbon::now()->addHour()->addMinute()->subSecond();
        $tournamentStartingIn1 = $this->getFutureTournaments($nextHour, $nextHourAnOneMinute);

        $now = Carbon::now();
        $oneMinuteFromNow = Carbon::now()->addMinute()->subSecond();
        $readyToCheckInTournaments = Tournament::query()
            ->whereRaw("started_at - INTERVAL check_in_period MINUTE >= {$now->format('Y-m-d H:i:s')}")
            ->whereRaw("started_at - INTERVAL check_in_period MINUTE <= {$oneMinuteFromNow->format('Y-m-d H:i:s')}")
            ->get()
            ->all();

        dispatch(new NotifyAboutTournamentStateChanges($tournamentStartingIn24, 'heads_up_24'));
        dispatch(new NotifyAboutTournamentStateChanges($tournamentStartingIn1, 'heads_up_1'));
        dispatch(new NotifyAboutTournamentStateChanges($readyToCheckInTournaments, 'checkin_started'));

    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getFutureTournaments(Carbon $start, Carbon $end)
    {
        return Tournament::query()
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->get()
            ->all();
    }
}
