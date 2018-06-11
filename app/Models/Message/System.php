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

    protected $notify = [
        'join', 'part', 'logout', 'timeout',
        'settings', 'status',
        'promote', 'demote', 'banned', 'unbanned', 'kicked', 'moved',
        'slow_timer_on', 'slow_timer_off'
    ];

    private $strings = [

        // Login messages
        'join' => ":user joins the channel.",
        'part' => ":user leaves the channel.",
        'logout' => ":user logs out from chat.",
        'force_logout' => "You have been logged out.",
        'timeout' => ":user's connection timed out.",

        // Channel
        'topic' => ":user set the topic to: :topic",
        'settings.access' => ":user set the channel visibility to :option.",
        'settings.persist' => ":user set the channel to :expires.",

        // Statuses
        'status.is' => ":user is :verb: :status.",
        'status.back' => ":user has returned.",

        // Staff
        'suspended' => ":user's account has been suspended.",
        'promote' => ":user promoted :target to :role.",
        'demote' => ":user demoted :target to :role.",
        'banned.staff' => ":user banned :target from chat.",
        'banned.others' => ":target was banned from chat.",
        'unbanned.staff' => ":user revoked :target's ban.",
        'unbanned.others' => ":target's ban was revoked.",
        'kick' => "You have been kicked from :channel.",
        'kicked.staff' => ":user kicked :target from the channel.",
        'kicked.others' => ":target was kicked from the channel.",
        'moved.staff' => ":user moved :target from :here to :there.",
        'moved.others' => ":target was moved from :here to :there.",
        'slow_timer_on.staff' => ":user has slowed the channel by :timer seconds.",
        'slow_timer_on.others' => "The channel has been slowed by :timer seconds.",
        'slow_timer_off.staff' => ":user removed the slow timer.",
        'slow_timer_off.others' => "The slow timer was removed.",

        // Actions
        'roll.single' => ":user rolled :roll and got :total.",
        'roll.many' => ":user rolled :roll and got :total. (The rolls were :rolls.)",
        'tarot' => ":user drew a Tarot card and got :card.",

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

    /**
     * @param string $key
     * @param array $replacements
     * @return string
     */
    private function trans($key, $replacements = [])
    {
        return strtr($this->strings[$key], $replacements);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    | Mutators and accessors
    |
    */

    public function getNotificationType()
    {
        return $this->attributes['message'];
    }

    public function getIsOwnMessageAttribute() {
        if (isset($this->context['user'])) {
            if ($this->context['user'] === Auth::user()->name) {
                return true;
            }
        }

        return false;
    }

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
                return $this->trans('join', [
                    ':user' => $this->context['user']
                ]);
            }

            if ($message == 'part') {
                return $this->trans('part', [
                    ':user' => $this->context['user']
                ]);
            }

            if ($message == 'logout') {
                return $this->trans('logout', [
                    ':user' => $this->context['user']
                ]);
            }

            if ($message == 'force_logout') {
                return $this->trans('force_logout');
            }

            if ($message == 'timeout') {
                return $this->trans('timeout', [
                    ':user' => $this->context['user']
                ]);
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
                return $this->trans('topic', [
                    ':user' => $this->context['user'],
                    ':topic' => $this->context['topic'],
                ]);
            }

            if ($message == 'current_topic') {
                return $this->context;
            }

            if ($message == 'settings') {
                if ($this->context['command'] == 'public') {
                    return $this->trans('settings.access', [
                        ':user' => $this->context['user'],
                        ':option' => $this->context['option'] == 'yes' ? 'public' : 'private',
                    ]);
                }

                if ($this->context['command'] == 'private') {
                    return $this->trans('settings.access', [
                        ':user' => $this->context['user'],
                        ':option' => $this->context['option'] == 'yes' ? 'private' : 'public',
                    ]);
                }

                if ($this->context['command'] == 'expire') {
                    return $this->trans('settings.persist', [
                        ':user' => $this->context['user'],
                        ':expires' => $this->context['option'] == 'no' ? 'never expire' : 'expire when unused',
                    ]);
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
                $status = $this->context['status'];

                if ($status == 'online') {
                    return $this->trans('status.back', [
                        ':user' => $this->context['user'],
                    ]);
                }

                $persist = Online::getPersistStatuses();

                return $this->trans('status.is', [
                    ':user' => $this->context['user'],
                    ':verb' => in_array($status, $persist) ? 'busy' : 'away',
                    ':status' => $status,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Staff
            |--------------------------------------------------------------------------
            |
            | Staff and moderation messages
            |
            */

            if ($message == 'suspended') {
                return $this->trans('suspended', [
                    ':user' => $this->context['target'],
                ]);
            }

            if ($message == 'promote') {
                return $this->trans('promote', [
                    ':user' => $this->context['user'],
                    ':target' => $this->context['target'],
                    ':role' => $this->context['role'],
                ]);
            }

            if ($message == 'demote') {
                return $this->trans('demote', [
                    ':user' => $this->context['user'],
                    ':target' => $this->context['target'],
                    ':role' => $this->context['role'],
                ]);
            }

            if ($message == 'banned') {
                if (Auth::user()->isStaff() && !is_null($this->context['user'])) {
                    return $this->trans('banned.staff', [
                        ':user' => $this->context['user'],
                        ':target' => $this->context['target'],
                    ]);
                }

                return $this->trans('banned.others', [
                    ':target' => $this->context['target'],
                ]);
            }

            if ($message == 'unbanned') {
                if (Auth::user()->isStaff()) {
                    return $this->trans('unbanned.staff', [
                        ':user' => $this->context['user'],
                        ':target' => $this->context['target'],
                    ]);
                }

                return $this->trans('unbanned.others', [
                    ':target' => $this->context['target'],
                ]);
            }

            if ($message == 'kick') {
                return $this->trans('kick', [
                    ':channel' => $this->context['channel'],
                ]);
            }

            if ($message == 'kicked') {
                if ($this->channel instanceof Channel) {
                    if ($this->channel->isStaff(Auth::user())) {
                        return $this->trans('kicked.staff', [
                            ':user' => $this->context['user'],
                            ':target' => $this->context['target'],
                        ]);
                    }
                }

                return $this->trans('kicked.others', [
                    ':target' => $this->context['target'],
                ]);
            }

            if ($message == 'moved') {
                $user = $this->context['user'];
                $target = $this->context['target'];
                $oldChannel = $this->context['old_channel'];
                $newChannel = $this->context['new_channel'];

                $here = $this->channel->name == $oldChannel ? 'this channel' : $oldChannel;
                $there = $this->channel->name == $newChannel ? 'this channel' : $newChannel;

                if ($this->channel instanceof Channel) {
                    if ($this->channel->isStaff(Auth::user())) {
                        return $this->trans('moved.staff', [
                            ':user' => $user,
                            ':target' => ($target == Auth::user()->name ? 'you' : $target),
                            ':here' => $here,
                            ':there' => $there,
                        ]);
                    }
                }

                return $this->trans('moved.others', [
                    ':target' => ($target == Auth::user()->name ? 'You were' : "$target was"),
                    ':here' => $here,
                    ':there' => $there,
                ]);
            }

            if ($message == 'slow_timer_on') {
                if ($this->channel instanceof Channel) {
                    if ($this->channel->isStaff(Auth::user())) {
                        return $this->trans('slow_timer_on.staff', [
                            ':user' => $this->context['user'],
                            ':timer' => $this->context['timer'],
                        ]);
                    }
                }

                return $this->trans('slow_timer_on.others', [
                    ':timer' => $this->context['timer'],
                ]);
            }

            if ($message == 'slow_timer_off') {
                $user = $this->context['user'];

                if ($this->channel instanceof Channel) {
                    if ($this->channel->isStaff(Auth::user())) {
                        return $this->trans('slow_timer_off.staff', [
                            ':user' => $this->context['user'],
                        ]);
                    }
                }

                return $this->trans('slow_timer_off.others');
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
                if (count($this->context['result']) == 1) {
                    return $this->trans('roll.single', [
                        ':user' => $this->context['user'],
                        ':roll' => $this->context['roll'],
                        ':total' => $this->context['total'],
                    ]);
                }

                return $this->trans('roll.many', [
                    ':user' => $this->context['user'],
                    ':roll' => $this->context['roll'],
                    ':total' => $this->context['total'],
                    ':rolls' => implode(', ', $this->context['result']),
                ]);
            }

            if ($message == 'tarot') {
                return $this->trans('tarot', [
                    ':user' => $this->context['user'],
                    ':card' => $this->context['tarot'],
                ]);
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
            \Log::error($e->getTraceAsString());

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
