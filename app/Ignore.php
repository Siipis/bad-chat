<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ignore extends Model
{
    public $timestamps = false;

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

    public function target()
    {
        return $this->belongsTo('App\User');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Helper methods
    |
    */

    public static function exists(User $user, User $target)
    {
        $instance = new static;

        return $instance->newQuery()->where('user_id', $user->id)->where('target_id', $target->id)->count() > 0;
    }

    public static function remove(User $user, User $target)
    {
        $instance = new static;

        return $instance->newQuery()->where('user_id', $user->id)->where('target_id', $target->id)->delete();
    }
}
