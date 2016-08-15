<?php

namespace App\Console;

use App\Channel;
use App\Login;
use App\Online;
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
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function() {
            Login::clearChannels();
            
            Online::clearOnline();
        })->everyMinute();

        $schedule->call(function() {
            Channel::expired()->delete();
        })->daily();
    }
}
