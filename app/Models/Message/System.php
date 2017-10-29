<?php

namespace App\Models\Message;

use App\Channel;
use App\Message;
use App\Online;
use App\Traits\ChannelMessage;
use App\Traits\TargettedMessage;
use App\Traits\UserMessage;
use App\User;
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
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Helpers for common messages
    |
    */

    public static function announceLogout(User $user, $channels)
    {
        foreach ($channels as $channel) {
            if ($channel instanceof Channel) {
                $channel->messages()->save(new self([
                    'message' => 'logout',
                    'context' => [
                        'user' => $user->name,
                    ]
                ]));
            }
        }
    }

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

        if ($this->attributes['message'] == 'roll') {
            return 'Roll';
        }

        if ($this->attributes['message'] == 'delete_row') {
            return $this->attributes['message'];
        }

        return config('chat.names.system');
    }

    public function getMessageAttribute($message)
    {
        try {
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

                return "$user set the topic to: $topic";
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
                    return "$user has returned.";
                }

                $persist = Online::getPersistStatuses();

                $verb = in_array($status, $persist) ? 'busy' : 'away';

                return "$user is $verb: $status.";
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

            if ($message == 'suspended') {
                return "$this->context has been suspended from chat.";
            }

            if ($message == 'banned') {
                $user = isset($this->context['user']) ? $this->context['user'] : "A staff member";
                $target = isset($this->context['target']) ? $this->context['target'] : "someone";

                if (Auth::user()->isStaff() && !is_null($user)) {
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

            if ($message == 'slow_timer_on') {
                $user = $this->context['user'];
                $timer = $this->context['timer'];

                if ($this->channel instanceof Channel) {
                    if ($this->channel->isStaff(Auth::user())) {
                        return "$user has slowed the channel by $timer seconds.";
                    }
                }

                return "The channel has been slowed by $timer seconds.";
            }

            if ($message == 'slow_timer_off') {
                $user = $this->context['user'];

                if ($this->channel instanceof Channel) {
                    if ($this->channel->isStaff(Auth::user())) {
                        return "$user removed the slow timer.";
                    }
                }

                return "The slow timer has been removed.";
            }

            /*
            |--------------------------------------------------------------------------
            | Actions
            |--------------------------------------------------------------------------
            |
            | Action specific messages
            |
            */

            if ($message == 'roll') {
                $user = $this->context['user'];
                $roll = $this->context['roll'];
                $result = $this->context['result'];
                $total = $this->context['total'];

                if (count($result) == 1) {
                    return "$user rolled $roll and got $total.";
                }

                $resultList = implode(', ', $result);

                return "$user rolled $roll and got $total. (The rolls were $resultList.)";
            }

            if ($message == 'tarot') {
                $user = $this->context['user'];
                $tarot = $this->context['tarot'];

                return "$user drew a Tarot card and got $tarot.";
            }

            /*
            |--------------------------------------------------------------------------
            | Data
            |--------------------------------------------------------------------------
            |
            | Data messages used for backend
            |
            */

            if ($message == 'delete_row') {
                return $this->context;
            }

        } catch (\Exception $e) {
            return "A database error occurred. Please report this!";
        }

        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        |
        | Return the unmodified message by default
        |
        */

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
