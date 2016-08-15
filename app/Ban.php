<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ban extends Model
{
    use SoftDeletes;

    protected $fillable = ['expires'];

    protected $hidden = ['user_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'expires'];

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

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    | Mutators and accessors
    |
    */

    public function getUntilAttribute()
    {
        return $this->expires->diffForHumans();
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    |
    | Custom query scopes
    |
    */

    public function scopeTarget($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeActive($query)
    {
        return $query->where('expires', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires', '<=', Carbon::now());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Helper methods
    |
    */

    /**
     * Creates a Carbon timestamp
     *
     * @param $duration
     * @param $units
     * @return Carbon
     */
    public static function createTimestamp($duration, $units)
    {
        if (!is_int($duration)) {
            throw new \InvalidArgumentException("Invalid integer [$duration].");
        }

        if (!in_array($units, config('chat.ban.units'))) {
            throw new \InvalidArgumentException("[$units] is not a valid unit.");
        }

        return Carbon::createFromTimestamp(strtotime("$duration $units"));
    }

    /**
     * Returns true if an active ban exists
     *
     * @param User $user
     * @return bool
     */
    public static function exists(User $user)
    {
        $instance = new static;

        return $instance->newQuery()->active()->target($user)->count() > 0;
    }

    /**
     * Returns an active ban if it exists
     *
     * @param User $user
     * @return Ban|null
     */
    public static function getExisting(User $user)
    {
        $instance = new static;

        return $instance->newQuery()->active()->target($user)->first();
    }
}
