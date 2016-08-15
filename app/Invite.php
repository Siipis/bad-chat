<?php

namespace App;

use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    public $timestamps = false;

    use HasRoles;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Model relationships
    |
    */

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function target()
    {
        return $this->belongsTo('App\User');
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
     * Returns true if a user is already invited
     *
     * @param Channel $channel
     * @param User $user
     * @return bool
     */
    public static function exists(Channel $channel, User $user)
    {
        $instance = new static;

        return $instance->getQuery()->where('channel_id', $channel->id)->where('target_id', $user->id)->count() > 0;
    }
}
