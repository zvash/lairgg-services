<?php

namespace App\Console;

use App\Jobs\ProcessDisqualifiedParticipants;
use App\Jobs\ProcessNotFinalOrders;
use App\ScheduledJobs\NotifyMatchWillNeedAttentionInFuture;
use App\ScheduledJobs\NotifyTournamentWillHaveAnActionInFuture;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(new ProcessDisqualifiedParticipants())
            ->everyMinute()
            ->name('process disqualified participants');

        $schedule->job(new ProcessNotFinalOrders())
            ->everyMinute()
            ->withoutOverlapping(5)
            ->name('process not final orders');

        $schedule->call(new NotifyMatchWillNeedAttentionInFuture())
            ->everyMinute()
            ->name('notify changes that will happen to tournaments');

        $schedule->call(new NotifyTournamentWillHaveAnActionInFuture())
            ->everyMinute()
            ->name('notify changes that will happen to tournaments');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
