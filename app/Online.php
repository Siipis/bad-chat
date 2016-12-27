<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Online extends Model
{
    public $timestamps = false;

    protected $fillable = ['status'];

    protected $persistStatuses = [
        'phone', 'working', 'gaming', 'art'
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
}
