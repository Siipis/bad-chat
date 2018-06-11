<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    public $primaryKey = 'user_id';

    public $timestamps = false;

    public $fillable = ['*'];

    public $visible = ['highlight', 'maxMessages', 'interval', 'timezone', 'notify'];

    protected $casts = [
        'notify' => 'array'
    ];

    public function owner()
    {
        return $this->hasOne('App\User');
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    | Setters and getters
    |
    */

    public function getChannelsAttribute($value)
    {
        if (empty($value) || is_null($value)) {
            return [];
        }

        return explode(PHP_EOL, $value);
    }

    public function setChannelsAttribute($value)
    {
        if (is_array($value)) {
            $value = implode('\n', $value);
        } else if ($value instanceof Collection) {
            $value = $value->implode('\n');
        }

        $this->attributes['channels'] = $value;
    }

    public function getHighlightAttribute($value)
    {
        if (empty($value) || is_null($value)) {
            return [];
        }

        return explode(PHP_EOL, $value);
    }

    public function setHighlightAttribute($value)
    {
        if (is_array($value)) {
            $value = implode('\n', $value);
        } else if ($value instanceof Collection) {
            $value = $value->implode('\n');
        }

        $this->attributes['highlight'] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Helper functions
    |
    */

    public static function user(User $user)
    {
        $settings = self::where('user_id', $user->id)->first();

        if (! $settings instanceof self) {
            $settings = self::defaults($user);
        }

        return $settings;
    }

    /**
     * Returns the default settings
     *
     * @param User $user
     * @return Settings
     */
    public static function defaults(User $user)
    {
        $instance = new self;

        $instance->channels = [];

        $instance->highlight = [
            $user->name
        ];

        $instance->notify = [
            'mentions' => true,
            'invites' => true,
            'channel' => true,
        ];

        $instance->maxMessages = 0;

        $instance->interval = null;
        
        $instance->timezone = 'UTC';

        $instance->theme = 'default';

        return $instance;
    }
}
