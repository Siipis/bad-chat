<?php

namespace App\Providers;

use App\Settings;
use File;
use View;
use Illuminate\Support\ServiceProvider;

class ViewComposerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $configPath = config('chat.configPath');

            $file = File::get($configPath);

            $config = json_decode($file, true);

            $theme = 'default';

            if (\Auth::check()) {
                $theme = Settings::user(\Auth::user())->theme;
            }

            $view->with([
                'appName' => $config['name'],
                'theme' => $theme,
            ]);

            return $view;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
