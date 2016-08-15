<?php

namespace App\Providers;

use App\Helpers\Access\Access;
use App\Helpers\Event\FrontLog;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('access', function($app) {
            return new Access;
        });

        $this->app->singleton('frontLog', function($app) {
            return new FrontLog;
        });

        $this->mergeConfigFrom(storage_path('app/config.php'), 'chat');
    }
}
