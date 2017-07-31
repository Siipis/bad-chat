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
        Commands\ChannelsClean::class,
        Commands\ChannelExpired::class,
        Commands\UploadClean::class,
        Commands\Statistics::class,
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
            try {
                Login::clearChannels();

                Online::clearOnline();

                \DB::disconnect('mysql');
            } catch (\Exception $e) {
                \Log::error($e->getMessage(), $e->getTrace());
            }
        })->everyMinute()->when(function() {
            return Online::count('id') > 0;
        });

        $schedule->call(function() {
            \Artisan::call('channel:clean');
            \Artisan::call('upload:clean');
        })->daily();

        $schedule->call(function() {
            \Artisan::call('chat:stats');
        })->weekly();
    }
}
