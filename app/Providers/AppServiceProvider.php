<?php

namespace App\Providers;

use App\Helpers\Access\Access;
use App\Helpers\Event\FrontLog;
use App\User;
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
        \Validator::extend('user', function($attribute, $value, $parameters, $validator) {
            $validator->addReplacer('user', function ($message, $attribute, $rule, $parameters) use ($parameters) {
                return str_replace(':value', $parameters[0], $message);
            });

            return User::whereName($parameters[0])->active()->count() > 0;
        });
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
