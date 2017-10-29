<?php

namespace App;

use App\Models\Message\System;
use Illuminate\Database\Eloquent\Model;

class Online extends Model
{
    public $timestamps = false;

    protected $fillable = ['status'];

    protected $persistStatuses = [
        'phone', 'working', 'gaming', 'art', 'tv',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Model relationships
    |
    */

    public function login()
    {
        return $this->belongsTo('App\Login');
    }

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Various helper methods
    |
    */

    public static function getPersistStatuses()
    {
        $instance =  new static;

        return $instance->persistStatuses;
    }

    public static function exists(Channel $channel, Login $login)
    {
        // TODO: make sure no duplicate logins are created

        $instance =  new static;

        return $instance->where('channel_id', $channel->id)->where('login_id', $login->id)->count() > 0;
    }

    public static function clearOnline()
    {
        foreach (self::all() as $online) {
            if ($online->login->isLoggedOut()) {
                $online->delete();
            }
        }
    }

    public static function logout(User $user, Login $login = null)
    {
        $logins = collect();

        if ($login instanceof Login) {
            $logins->push($login);
        } else {
            $logins = Login::online()->userId($user)->get();
        }

        foreach ($logins as $login) {
            if ($login instanceof Login) {
                System::announceLogout($user, $login->channels);

                foreach ($login->onlines as $online) {
                    $online->delete();
                }
            }
        }
    }

    public static function timeout(Login $login)
    {
        foreach ($login->onlines as $online) {
            if ($online instanceof Online) {
                $channel = $online->channel;

                $system = new System([
                    'message' => 'timeout',
                    'context' => [
                        'user' => $login->user->name
                    ],
                ]);

                $channel->messages()->save($system);

                $online->delete();
            }
        }
    }
}
