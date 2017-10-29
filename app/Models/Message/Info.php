<?php

namespace App\Models\Message;


use App\Message;
use App\Traits\ChannelMessage;
use App\Traits\TargettedMessage;
use App\Traits\UserMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Info extends Message
{
    use ChannelMessage, UserMessage, TargettedMessage;

    protected $table = 'messages';

    protected $require = ['target_id'];

    protected $attributes = [
        'type' => 'info'
    ];

    protected $isError = [
        'unknown_command',
        'not_permitted',
        'illegal_action',
        'user_not_found',
        'self_target',
        'channel_not_found',
        'join_error',
        'channel_name',
        'default_channel_error',
        'cannot_leave_channel',
        'public_channel',
        'private_chanel',
        'ignore_exists',
        'ignore_not_found',
        'ban_exists',
        'ban_not_found',
        'slowed',
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
        if (in_array($this->attributes['message'], $this->isError)) {
            return config('chat.names.error');
        }

        return config('chat.names.info');
    }

    public function getMessageAttribute($message)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | General errors
            |--------------------------------------------------------------------------
            |
            | Commonly occurring general purpose messages
            |
            */

            if ($message == 'unknown_command') {
                $command = $this->context;

                return "Unknown or wrongly worded command: $command";
            }

            if ($message == 'not_permitted') {
                $action = "perform that action";

                switch ($this->context) {
                    case 'whois':
                        $action = "see IP's";
                        break;
                    case 'ban':
                        $action = "ban that user";
                        break;
                    case 'unban':
                        $action = "unban that user";
                        break;
                    case 'kick':
                        $action = "kick that user here";
                        break;
                    case 'vouch' :
                        $action = "vouch at the moment";
                        break;
                    case 'promote':
                        $action = "promote that user";
                        break;
                    case 'demote':
                        $action = "demote that user";
                        break;
                    case 'join':
                        $action = "join that channel";
                        break;
                    case 'invite':
                        $action = "invite users to the channel";
                        break;
                    case 'uninvite':
                        $action = "uninvite users from the channel";
                        break;
                    case 'topic':
                        $action = "set the topic on this channel";
                        break;
                    case 'settings':
                        $action = "change the settings on this channel";
                        break;
                    case 'setting':
                        $action = "change that setting";
                        break;
                }

                return "You don't have permission to $action.";
            }

            if ($message == 'user_not_found') {
                $user = $this->context;

                return "Could not find user $user.";
            }

            if ($message == 'user_not_online') {
                $user = $this->context;

                return "$user is not online at the moment.";
            }

            if ($message == 'user_last_online') {
                $user = $this->context['user'];
                $seen = $this->context['seen'];

                if (is_null($seen)) {
                    return "$user has never been seen.";
                }

                if ($seen == 'right now') {
                    return "$user is online right now.";
                }

                return "$user was last seen $seen.";
            }

            if ($message == 'self_target') {
                return "You can't target yourself with that action.";
            }

            /*
            |--------------------------------------------------------------------------
            | Staff
            |--------------------------------------------------------------------------
            |
            | Staff specific messages
            |
            */

            if ($message == 'whois') {
                $user = $this->context['user'];
                $ip = $this->context['ip'];

                return "$user's IP address is $ip.";
            }

            /*
            |--------------------------------------------------------------------------
            | Channel
            |--------------------------------------------------------------------------
            |
            | Channel specific messages
            |
            */

            if ($message == 'channel_not_found') {
                $channel = $this->context;

                return "Could not find channel $channel.";
            }

            if ($message == 'join_error') {
                $channel = $this->context;

                return "Could not join channel $channel.";
            }

            if ($message == 'channel_name') {
                $channel = $this->context;

                $error = $channel > 0 ? "long" : "short";

                return "Channel name was too $error.";
            }

            if ($message == 'cannot_leave_channel') {
                return "Cannot leave the channel.";
            }

            if ($message == 'expires') {
                $expires = new Carbon($this->context);
                $diffForHumans = $expires->diffForHumans();

                return "The channel expires $diffForHumans.";
            }

            if ($message == 'about') {
                $name = $this->context['name'];
                $user = $this->context['user'];
                $access = $this->context['access'];
                $expires = $this->context['expires'];

                $diffForHumans = is_null($expires) ? 'never' : $expires->diffForHumans();

                return "Name: $name. Owner: $user. Access: $access. Expires: $diffForHumans.";
            }

            if ($message == 'public_channel') {
                return "The channel is public.";
            }

            if ($message == 'private_channel') {
                return "The channel is private.";
            }

            if ($message == 'find') {
                $user = $this->context['user'];
                $channels = $this->context['channels'];

                $channelList = implode(', ', $channels);

                return "$user is chatting in $channelList.";
            }

            if ($message == 'slowed') {
                $timer = $this->context;

                return "The channel is slowed. Please wait $timer more seconds.";
            }

            /*
            |--------------------------------------------------------------------------
            | Statuses
            |--------------------------------------------------------------------------
            |
            | Status specific messages
            |
            */

            if ($message == 'status_exists') {
                return "Your status is already $this->context.";
            }

            /*
            |--------------------------------------------------------------------------
            | Invites
            |--------------------------------------------------------------------------
            |
            | Invite specific messages
            |
            */

            if ($message == 'invite_exists') {
                $user = $this->context['user'];
                $channel = $this->context['channel'];

                return "$user has already been invited to $channel.";
            }

            if ($message == 'invite') {
                $user = $this->user->name;
                $target = $this->target->name;
                $channel = $this->context;

                if (\Auth::id() == $this->target->id) {
                    return "$user has invited you to join $channel.";
                } else {
                    return "You have invited $target to join $channel.";
                }
            }

            if ($message == 'uninvite') {
                $target = $this->target->name;
                $channel = $this->context;

                if (\Auth::id() == $this->target->id) {
                    return "You have been uninvited from $channel.";
                } else {
                    return "You have revoked $target's invite to $channel.";
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Vouches
            |--------------------------------------------------------------------------
            |
            | Vouch specific messages
            |
            */

            if ($message == 'vouched') {
                return "$this->context is now your protegee!";
            }

            if ($message == 'already_vouched') {
                return "You have already vouched for $this->context.";
            }

            if ($message == 'vouch_not_found') {
                return "No vouch was found for $this->context.";
            }

            if ($message == 'vouch_removed') {
                return "You no longer vouch for $this->context.";
            }

            if ($message == 'new_vouch') {
                return "$this->context just vouched for you. Congratulations!";
            }

            if ($message == 'protegees') {
                $protegees = $this->context;

                if (empty($protegees)) {
                    return "You have no protegees.";
                } else {
                    $protegees = str_list($protegees, true);

                    return "You vouch for $protegees.";
                }
            }

            if ($message == 'protectors') {
                $protectors = $this->context;

                if (empty($protectors)) {
                    return "You have no protectors.";
                } else {
                    $verb = count($protectors) == 1 ? 'vouches' : 'vouch';
                    $protectors = str_list($protectors, true);

                    return "$protectors $verb for you.";
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Ignores
            |--------------------------------------------------------------------------
            |
            | Ignore specific messages
            |
            */

            if ($message == 'ignore_exists') {
                $user = $this->context['user'];

                return "You have already ignored $user.";
            }

            if ($message == 'ignore_not_found') {
                $user = $this->context['user'];

                return "You can already see $user's messages'.";
            }

            if ($message == 'ignored') {
                $user = $this->context['user'];

                return "$user has been ignored and their messages won't be shown anymore.";
            }

            if ($message == 'unignored') {
                $user = $this->context['user'];

                return "$user is no longer ignored and their messages will be shown.";
            }

            /*
            |--------------------------------------------------------------------------
            | Bans
            |--------------------------------------------------------------------------
            |
            | Ban specific messages
            |
            */

            if ($message == 'ban_exists') {
                $user = $this->context['user'];
                $until = $this->context['until'];

                return "$user has already been banned until $until.";
            }

            if ($message == 'ban_not_found') {
                $user = $this->context['user'];

                return "No ban was found for $user.";
            }

            if ($message == 'banned') {
                $user = $this->context['user'];
                $duration = $this->context['duration'];

                return "$user has been banned for $duration.";
            }

            if ($message == 'unbanned') {
                $user = $this->context['user'];

                return "You have revoked the ban on $user.";
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
            $builder->where('type', 'whisper');
        });
    }
}
