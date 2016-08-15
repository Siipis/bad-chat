<?php

namespace App\Models\Message;


use App\Message;
use App\Traits\ChannelMessage;
use App\Traits\UserMessage;
use Illuminate\Database\Eloquent\Builder;

class Post extends Message
{
    use ChannelMessage, UserMessage;

    protected $table = 'messages';

    protected $require = ['channel_id', 'user_id'];

    protected $attributes = [
        'type' => 'post'
    ];

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
            $builder->where('type', 'post');
        });
    }
}
