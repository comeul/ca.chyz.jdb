<?php

namespace App\Console;

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
        Commands\CreateShowCache::class,
        Commands\CreateFeuilleDeRoutes::class,
        Commands\SendFeuillesDeRoutesEmails::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('jdb:set-cache')
            ->dailyAt('00:30')
            ->timezone('America/New_York');

        $schedule->command('jdb:set-cache')
            ->dailyAt('06:30')
            ->timezone('America/New_York');

        $schedule->command('jdb:set-cache')
            ->dailyAt('12:30')
            ->timezone('America/New_York');

        $schedule->command('jdb:set-cache')
            ->dailyAt('18:30')
            ->timezone('America/New_York');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
