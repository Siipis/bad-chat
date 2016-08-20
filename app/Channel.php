<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'access', 'expires'];

    protected $visible = [
        'name', 'topic', 'access', 'latest', 'changed'
    ];

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

    public function online()
    {
        return $this->hasMany('App\Online');
    }

    public function messages()
    {
        return $this->hasMany('App\Message');
    }

    public function invites()
    {
        return $this->hasMany('App\Invite');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Query Scopes
    |
    */

    public function scopeDefaults($query)
    {
        return $query->where('isDefault', true);
    }

    public function scopeExpired($query)
    {
        $expires = Carbon::now()->subDays(config('chat.channels.expire'));

        return $query->where('expires', '<=', $expires)->whereNull('deleted_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Static queries
    |--------------------------------------------------------------------------
    |
    | Custom find queries
    |
    */

    /**
     * @param $name
     * @return Channel|null
     */
    public static function findByName($name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * @param $name
     * @return Channel
     */
    public static function findByNameOrFail($name)
    {
        return static::where('name', $name)->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Various helper methods
    |
    */

    /**
     * Returns true if a channel is public
     *
     * @return bool
     */
    public function isPublic() {
        return $this->access == 'public';
    }

    public function isPrivate() {
        return !$this->isPublic();
    }

    /**
     * Returns true if a channel has expired
     *
     * @return bool
     */
    public function hasExpired()
    {
        return $this->expires <= Carbon::now();
    }

    /**
     * Postpones the expiration date
     * @param boolean $ignoreNull
     */
    public function touchExpires($ignoreNull = false)
    {
        if (is_null($this->expires) && !$ignoreNull) {
            return;
        }

        $this->expires = $this->freshTimestamp()->addDays(config('chat.channels.expire'));
        $this->save();
    }

    /**
     * Restores a deleted channel
     *
     * @param User|null $user
     * @return bool
     */
    public function restore(User $user = null)
    {
        if (!$this->trashed()) {
            return false;
        }

        parent::restore();
        $this->topic = null;
        $this->access = 'private';
        $this->touchExpires();

        if ($user instanceof User) {
            $user->channels()->save($this);
        }

        return true;
    }

    /**
     * Returns the channel role
     *
     * @param User $user
     * @return string
     */
    public function getRole(User $user)
    {
        if ($this->user->id == $user->id) {
            return 'admin';
        }

        if (!is_null($invite = $this->invites()->getQuery()->where('target_id', $user->id)->first())) {
            return $invite->role;
        }

        return $user->role;
    }

    /**
     * Returns true if the user is staff on the channel
     *
     * @param User $user
     * @return bool
     */
    public function isStaff(User $user)
    {
        if ($this->user->id == $user->id) {
            return true;
        }

        if ($user->isStaff()) {
            return true;
        }

        return $this->getRole($user) != 'member';
    }

    /**
     * Returns true if the user is admin on the channel
     *
     * @param User $user
     * @return bool
     */
    public function isAdmin(User $user)
    {
        return $this->user->id == $user->id || $this->getRole($user) == 'admin';
    }

    /**
     * Returns true if the user can access the channel
     *
     * @param User $user
     * @return bool
     */
    public function canJoin(User $user)
    {
        if ($this->access == 'public') {
            return true;
        }

        if ($this->user->id == $user->id) {
            return true;
        }

        if ($invite = $this->invites()->getQuery()->where('target_id', $user->id)->count() > 0) {
            return true;
        }

        return false;
    }
}
