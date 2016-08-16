<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::controller('account', 'AccountController');
    Route::controller('config', 'ConfigController');
    Route::controller('logs', 'LogsController');
    Route::controller('roles', 'RolesController');
    Route::controller('users', 'UsersController');

    Route::controller('password', 'Auth\PasswordController');

    Route::controller('chat', 'ChatController');

    Route::get('scheduled', function() {
        return Artisan::call('schedule:run');
    });
    Route::controller('/', 'LoginController');
});
