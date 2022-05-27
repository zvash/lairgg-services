<?php

namespace App\Console;

use App\Jobs\ProcessNotFinalOrders;
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
        $schedule->job(new ProcessNotFinalOrders())
            ->everyMinute()
            ->withoutOverlapping(5)
            ->name('process not final orders');

        $schedule->call(new NotifyTournamentWillHaveAnActionInFuture())
            ->everyMinute()
            ->withoutOverlapping(2)
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
