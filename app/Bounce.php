<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bounce extends Model
{
    protected $fillable = ['*'];

    protected $hidden = ['*'];

    /**
     * Stores a new bounce in the database
     */
    public static function add()
    {
        $instance = new self;

        $instance->ip = $ip = $instance->getUserIp();

        $instance->save();
    }

    /**
     * Returns the previous bounce
     * not made by the current user
     *
     * @return Bounce
     */
    public static function previous()
    {
        $instance = new self;

        $ip = $instance->getUserIp();

        $bounce = static::where('ip', '!=', $ip)->orderBy('updated_at', 'desc')->first();

        $login = Login::where('ip', '!=', $ip)->orderBy('updated_at', 'desc')->first();

        if ($login->updated_at > $bounce->updated_at) {
            // Copy Login instance to a Bounce
            $transform = new self;

            $transform->ip = $login->ip;
            $transform->created_at = $login->created_at;
            $transform->updated_at = $login->updated_at;

            return $transform;
        }

        return $bounce;
    }

    /**
     * @return string
     */
    private function getUserIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
}
