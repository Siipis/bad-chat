<?php

namespace App;

use App\Models\Message\System;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Session;

class Login extends Model
{
    protected $hidden = ['*'];

    public $timestamps = ['created_at', 'updated_at', 'logout_at'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Model relationships
    |
    */

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function channels()
    {
        return $this->belongsToMany('App\Channel', 'onlines');
    }

    public function onlines()
    {
        return $this->hasMany('App\Online');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Custom query scopes
    |
    */

    public function scopeOnline($query)
    {
        return $query->whereNull('logout_at');
    }

    public function scopeUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeExpired($query)
    {
        $expires = Carbon::now()->subMinutes(config('chat.login.timeout'));

        return $query->where('updated_at', '<=', $expires)->whereNull('logout_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Accessors and mutators
    |
    */

    public function getTimestampAttribute()
    {
        $weekAgo = Carbon::now()->subDays(7);

        return $this->created_at > $weekAgo ? $this->created_at->diffForHumans() : $this->created_at->toDayDateTimeString();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper functions
    |--------------------------------------------------------------------------
    |
    | Various helper methods
    |
    */

    /**
     * Creates a login trace
     *
     * @return bool
     */
    public static function trace()
    {
        $user = Auth::user();

        if (!is_null($login = self::active($user))) {
            // TODO: log behaviour as suspicious

            $login->touch();

            return false;
        }

        $login = new self;

        $login->ip = $_SERVER['REMOTE_ADDR'];
        $login->agent = $_SERVER['HTTP_USER_AGENT'];
        $login->user()->associate($user);

        $login->save();

        Session::set('login', $login->id); // Store login instance in case of disconnects

        return true;
    }

    public static function verify($login = null)
    {
        $user = Auth::user();

        $instance = new static;

        if (is_null($login)) {
            // Default to looking for an active login
            $login = $instance->online()->user($user)->first();
        }

        if (!is_null($login)) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $agent = $_SERVER['HTTP_USER_AGENT'];

            if ($login->agent == $agent) {
                if ($login->ip != $ip) {
                    $login->ip = $ip;

                    $login->save();
                }

                return true;
            }
        }

        return false;
    }

    public static function attemptReconnect()
    {
        if (Auth::check() && Session::has('login')) {
            $instance = new static;

            $login = $instance->find(Session::get('login'));

            if ($login->user->id == Auth::id() && $instance->verify($login)) {
                $login->unLogout();

                return true;
            }
        }

        return false;
    }

    /**
     * Attempts to log out a user
     *
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public static function logout(User $user = null)
    {
        if (is_null($user)) {
            if (Auth::guest()) {
                return false;
            }

            $user = Auth::user();
        }

        Auth::logout();

        $login = Login::active($user);

        if ($login instanceof Login) {
            foreach ($login->channels as $channel) {
                $channel->messages()->save(new System([
                    'message' => 'logout',
                    'context' => [
                        'user' => $user->name,
                    ]
                ]));
            }

            foreach ($login->onlines as $online) {
                $online->delete();
            }

            $login->touchLogout();

            return true;
        }

        return false;
    }

    /**
     * Returns an active login if one exists
     *
     * @param User $user
     * @return Login|null
     */
    public static function active(User $user)
    {
        $expires = Carbon::now()->subMinutes(config('chat.login.timeout'));

        $instance = new static;

        return $instance->newQuery()->where('updated_at', '>', $expires)->whereNull('logout_at')->where('user_id', $user->id)->first();
    }

    /**
     * Returns true if a user is in the chat
     *
     * @param User $user
     * @return bool
     */
    public static function isChatting(User $user)
    {
        if (!is_null($login = self::active($user))) {
            return $login->channels->count() > 0;
        }

        return false;
    }

    /**
     * Returns true if the login has been ended
     *
     * @return bool
     */
    public function isLoggedOut()
    {
        return !is_null($this->logout_at);
    }

    /**
     * Sets the logout timestamp
     */
    public function touchLogout()
    {
        $this->logout_at = $this->freshTimestamp();
        $this->save();
    }

    /**
     * Removes the logout timestamp
     */
    public function unLogout()
    {
        $this->logout_at = null;
        $this->save();
    }

    /**
     * Clears all channels of expired logins
     */
    public static function clearChannels()
    {
        foreach (self::expired()->get() as $login) {
            $login->touchLogout();

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



    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    |
    | Status management
    |
    */

    /**
     * Returns true if a status exists
     *
     * @param null|string $status
     * @return bool
     */
    public function hasStatus($status = null)
    {
        $online = new Online;

        if (is_null($status)) {
            return $online->newQuery()->where('login_id', $this->id)->where('status', '!=', 'online')->count() > 0;
        }

        return $online->newQuery()->where('login_id', $this->id)->where('status', $status)->count() > 0;
    }

    /**
     * Sets the status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $online = new Online;

        \DB::table($online->getTable())->where('login_id', $this->id)->update([
            'status' => $status
        ]);
    }

    /**
     * Returns the current login status
     *
     * @return null|string
     */
    public function getStatus()
    {
        $online = new Online;

        $first = $online->newQuery()->where('login_id', $this->id)->firstOrFail();

        if ($first instanceof Online) {
            return $first->status;
        }

        return null;
    }
}
