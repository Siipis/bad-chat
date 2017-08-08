<?php

namespace App;

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

    public function scopeUserId($query, User $user)
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
            // Close the previous session
            $login->close();
        }

        $login = new self;

        $login->ip = $_SERVER['REMOTE_ADDR'];
        $login->agent = $_SERVER['HTTP_USER_AGENT'];
        $login->user()->associate($user);

        $login->save();

        Session::set('login', $login->id); // Store the current login instance

        return true;
    }

    public static function verify($login = null)
    {
        $user = Auth::user();

        $instance = new static;

        if (is_null($login)) {
            // Default to looking for an active login
            $login = $instance->online()->userId($user)->first();
        }

        if (!is_null($login)) {
            if (config('security.verify.agent')) {
                $agent = $_SERVER['HTTP_USER_AGENT'];

                if ($login->agent != $agent) {
                    return false;
                }
            }

            if (config('security.verify.ip')) {
                $ip = $_SERVER['REMOTE_ADDR'];

                if ($login->ip != $ip) {
                    if (config('security.allow.ipChange')) {
                        $login->ip = $ip;

                        $login->save();
                    } else {
                        return false;
                    }
                }
            }

            if (config('security.verify.session')) {
                if ($login->id != Session::get('login')) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public static function attemptReconnect()
    {
        $attempts = Session::get('login_attempts');

        if (is_null($attempts)) {
            $attempts = 1;
        } else {
            $attempts++;
        }

        Session::set('login_attempts', $attempts);

        if ($attempts > 3) {
            Login::logout();

            return false;
        }

        if (Auth::check() && Session::has('login')) {
            $instance = new static;

            $login = $instance->find(Session::get('login'));

            if (!$login->isClosed() && $login->user->id == Auth::id() && $instance->verify($login)) {
                $login->unLogout();

                Session::set('login_attempts', 0);

                return true;
            }
        }

        Login::logout();

        return false;
    }

    /**
     * Attempts to log out a user
     *
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public static function logout(User $user = null, $clearSession = true)
    {
        if (is_null($user)) {
            if (Auth::guest()) {
                return false;
            }

            $user = Auth::user();
        }

        Online::logout($user);

        if ($clearSession && Auth::id() == $user->id) {
            Login::clean();
        }

        return true;
    }

    /**
     * Remove all active logins
     *
     * @param bool $removeSession
     */
    public static function clean($removeSession = true)
    {
        if (Auth::check()) {
            $user = Auth::user();

            Online::logout(Auth::user());

            foreach (Login::userId($user)->online()->get() as $login) {
                $login->touchLogout();

                $login->close();
            }

            if ($removeSession) {
                Auth::logout();

                Session::remove('login');
            }
        }
    }

    /**
     * Returns an active login if one exists
     *
     * @param User $user
     * @return Login|null
     */
    public static function active(User $user)
    {
        $instance = new static;

        $expires = Carbon::now()->subMinutes(config('chat.login.timeout'));

        $query = $instance->newQuery()
            ->where('updated_at', '>', $expires)
            ->whereNull('logout_at')
            ->where('closed', false)
            ->where('user_id', $user->id);

        if ($user == Auth::user()) {
            // If a session key exists, use it to fetch the login instance
            if (Session::has('login')) {
                $session = (int)Session::get('login');

                return $query->where('id', $session)->where('closed', false)->first();
            }
        }

        // Otherwise, fetch the latest active login
        return $query->first();
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
     * Returns true if a login has been closed
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Sets the login to closed
     */
    public function close()
    {
        self::logout($this->user, false);

        $this->closed = true;
        $this->save();
    }

    /**
     * Clears all channels of expired logins
     */
    public static function clearChannels()
    {
        foreach (self::expired()->get() as $login) {
            $login->touchLogout();

            Online::timeout($login);
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
