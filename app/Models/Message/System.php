<?php

namespace App\Models\Message;

use App\Channel;
use App\Message;
use App\Traits\ChannelMessage;
use App\Traits\TargettedMessage;
use App\Traits\UserMessage;
use Auth;
use Illuminate\Database\Eloquent\Builder;

class System extends Message
{
    use ChannelMessage, UserMessage, TargettedMessage;

    protected $table = 'messages';

    protected $attributes = [
        'type' => 'system'
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    | Mutators and accessors
    |
    */

    public function getNameAttribute()
    {
        if ($this->attributes['message'] == 'current_topic') {
            return 'Topic';
        }

        if ($this->attributes['message'] == 'delete_row') {
            return $this->context;
        }

        return config('chat.names.system');
    }

    public function getMessageAttribute($message)
    {
        /*
        |--------------------------------------------------------------------------
        | Login messages
        |--------------------------------------------------------------------------
        |
        | Login specific messages
        |
        */

        if ($message == 'join') {
            $user = $this->context['user'];

            return "$user joins the channel.";
        }

        if ($message == 'part') {
            $user = $this->context['user'];

            return "$user leaves the channel.";
        }

        if ($message == 'logout') {
            $user = $this->context['user'];

            return "$user logs out from chat.";
        }

        if ($message == 'force_logout') {
            return "You have been logged out.";
        }

        if ($message == 'timeout') {
            $user = $this->context['user'];

            return "$user's connection timed out.";
        }

        /*
        |--------------------------------------------------------------------------
        | Channel
        |--------------------------------------------------------------------------
        |
        | Channel specific messages
        |
        */
        if ($message == 'topic') {
            $user = $this->context['user'];
            $topic = $this->context['topic'];

            return "$user set the topic to \"$topic\".";
        }

        if ($message == 'current_topic') {
            return $this->context;
        }

        if ($message == 'settings') {
            $user = $this->context['user'];
            $command = $this->context['command'];
            $option = $this->context['option'];

            if ($command == 'access') {
                return "$user set the channel visibility to $option.";
            }

            if ($command == 'persist') {
                $expires = $option == 'on' ? 'never expire' : 'expire when unused';

                return "$user set the channel to $expires.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Statuses
        |--------------------------------------------------------------------------
        |
        | Status specific messages
        |
        */

        if ($message == 'status') {
            $user = $this->context['user'];
            $status = $this->context['status'];

            if ($status == 'online') {
                $oldStatus = $this->context['old_status'];

                return "$user is no longer $oldStatus.";
            }

            return "$user has set their status to $status.";
        }

        /*
        |--------------------------------------------------------------------------
        | Staff
        |--------------------------------------------------------------------------
        |
        | Staff and moderation messages
        |
        */

        if ($message == 'promote') {
            $user = $this->context['user'];
            $target = $this->context['target'];
            $role = $this->context['role'];

            return "$user promoted $target to $role.";
        }

        if ($message == 'demote') {
            $user = $this->context['user'];
            $target = $this->context['target'];
            $role = $this->context['role'];

            return "$user demoted $target to $role.";
        }

        if ($message == 'banned') {
            $user = $this->context['user'];
            $target = $this->context['target'];

            if (Auth::user()->isStaff()) {
                return "$user banned $target from chat.";
            }

            return "$target was banned from chat.";
        }

        if ($message == 'unbanned') {
            $user = $this->context['user'];
            $target = $this->context['target'];

            if (Auth::user()->isStaff()) {
                return "$user revoked $target's ban.";
            }

            return "$target's ban has been revoked.";
        }

        if ($message == 'kick') {
            $channel = $this->context['channel'];

            return "You have been kicked from $channel.";
        }

        if ($message == 'kicked') {
            $user = $this->context['user'];
            $target = $this->context['target'];

            if ($this->channel instanceof Channel) {
                if ($this->channel->isStaff(Auth::user())) {
                    return "$user kicked $target from the channel.";
                }
            }

            return "$target was kicked from the channel.";
        }

        return $message;
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
            $builder->where('type', 'system');
        });
    }
}
