<?php

namespace App\Models\Message;

use App\Message;
use App\Traits\TargettedMessage;
use App\Traits\UserMessage;
use Illuminate\Database\Eloquent\Builder;

class Whisper extends Message
{
    use UserMessage, TargettedMessage;

    protected $table = 'messages';

    protected $require = ['user_id', 'target_id'];

    protected $appends = [
        'timestamp', 'name', 'receiver', 'isOwnMessage', 'whisperDirection', 'notify'
    ];

    protected $attributes = [
        'type' => 'whisper'
    ];

    protected $notify = true;

    public function getWhisperDirectionAttribute()
    {
        return $this->user->id == \Auth::id() ? 'from' : 'to';
    }

    public function getNotificationType()
    {
        return 'whisper';
    }


    /*
    |--------------------------------------------------------------------------
    | Override
    |--------------------------------------------------------------------------
    |
    | Override default behaviour
    |
    */

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 'whisper');
        });
    }
}
