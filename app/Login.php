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
     * Generates a unique login key
     *
     * @return string
     */
    public static function key()
    {
        return uniqid();
    }

    /**
     * Creates a login trace
     *
     * @return bool
     */
    public static function trace()
    {
        $user = Auth::user();

        self::logout($user, false);

        $login = new self;

        $login->ip = $_SERVER['REMOTE_ADDR'];
        $login->agent = $_SERVER['HTTP_USER_AGENT'];
        $login->key = self::key();
        $login->user()->associate($user);

        $login->save();

        Session::set('login_id', $login->id); // Store the current login instance
        Session::set('login_key', $login->key);

        return true;
    }

    public static function verifySession()
    {
        if (Session::has('login_id') && Session::has('login_key')) {
            $login = self::find(Session('login_id'));

            if (!$login->isClosed() && $login->key == Session('login_key')) {
                return true;
            }
        }

        return false;
    }

    public static function verify($login = null)
    {
        $instance = new static;

        if (is_null($login)) {
            // Default to looking for an active login
            $login = $instance->active();
        }

        if ($login instanceof Login) {
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
                if ($login->id != Session::get('login_id')) {
                    return false;
                }
            }

            return $login->verifySession() && !$login->isLoggedOut();
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

        if (Auth::check() && Session::has('login_id')) {
            $instance = new static;

            $login = $instance->find(Session::get('login_id'));

            if ($login->isClosed()) {
                Auth::logout();

                Session::clear();

                return false;
            }

            if ($login->user->id == Auth::id() && $login->key == Session::get('login_key')) {
                $login->unLogout();

                if (Login::verify()) {
                    Session::set('login_attempts', 0);

                    return true;
                }
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

        foreach (Login::userId($user)->online()->get() as $login) {
            $login->close();
        }

        if ($clearSession && Auth::id() == $user->id) {
            self::clearSession();
        }

        return true;
    }

    /**
     * Removes the user session
     */
    public static function clearSession()
    {
        Session::clear();

        Auth::logout();
    }

    /**
     * Returns an active login if one exists
     *
     * @param User $user
     * @return Login|null
     */
    public static function active(User $user = null)
    {
        if (is_null($user)) {
            $user = Auth::user();
        }

        $instance = new static;

        $expires = Carbon::now()->subMinutes(config('chat.login.timeout'));

        $query = $instance->newQuery()
            ->where('updated_at', '>', $expires)
            ->whereNull('logout_at')
            ->where('closed', false)
            ->where('user_id', $user->id);

        if ($user == Auth::user()) {
            if (Session::has('login_id') && Session::has('login_key')) {
                $login_id = (int) Session::get('login_id');
                $login_key = Session::get('login_key');

                return $query->where('id', $login_id)->where('key', $login_key)->first();
            }
        } else {
            return $query->first();
        }

        return null;
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
        $this->updated_at = $this->freshTimestamp();
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
        $this->key = null;
        $this->closed = true;

        $this->touchLogout();
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
