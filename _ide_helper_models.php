<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Ban
 *
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $expires
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read mixed $until
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban expired()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Ban onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban target(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Ban withoutTrashed()
 */
	class Ban extends \Eloquent {}
}

namespace App{
/**
 * App\Bounce
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $ip
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bounce whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bounce whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bounce whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bounce whereUpdatedAt($value)
 */
	class Bounce extends \Eloquent {}
}

namespace App{
/**
 * App\Channel
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $topic
 * @property string $access
 * @property string|null $expires
 * @property int $isDefault
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $slowed
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Invite[] $invites
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Message[] $messages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Online[] $online
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel defaults()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel expired()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Channel onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereSlowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Channel whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Channel withoutTrashed()
 */
	class Channel extends \Eloquent {}
}

namespace App{
/**
 * App\Conversation
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int $user_id
 * @property string|null $title
 * @property string $message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $names
 * @property-read mixed $read_at
 * @property-read \App\Conversation|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $participants
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Conversation[] $responses
 * @property-read \App\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation parent(\App\Conversation $conversation)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation readable()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation threads()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation trashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation visible()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversation whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation withoutTrashed()
 */
	class Conversation extends \Eloquent {}
}

namespace App{
/**
 * App\Event
 *
 * @property int $id
 * @property string $level
 * @property int $code
 * @property string $event
 * @property array $context
 * @property string $url
 * @property string $ip
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Event whereUrl($value)
 */
	class Event extends \Eloquent {}
}

namespace App{
/**
 * App\Ignore
 *
 * @property int $id
 * @property int $user_id
 * @property int $target_id
 * @property-read \App\User $target
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ignore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ignore whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ignore whereUserId($value)
 */
	class Ignore extends \Eloquent {}
}

namespace App{
/**
 * App\Invite
 *
 * @property int $id
 * @property int $channel_id
 * @property int $user_id
 * @property int $target_id
 * @property int $role
 * @property-read \App\Channel $channel
 * @property-read mixed $role_num
 * @property-read \App\User $target
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Invite whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Invite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Invite whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Invite whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Invite whereUserId($value)
 */
	class Invite extends \Eloquent {}
}

namespace App{
/**
 * App\Login
 *
 * @property int $id
 * @property int $user_id
 * @property string $ip
 * @property string $agent
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $logout_at
 * @property int $closed
 * @property string $key
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Channel[] $channels
 * @property-read mixed $timestamp
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Online[] $onlines
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login expired()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login online()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login userId(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereLogoutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Login whereUserId($value)
 */
	class Login extends \Eloquent {}
}

namespace App{
/**
 * App\Message
 *
 * @property int $id
 * @property string $type
 * @property int|null $channel_id
 * @property int|null $user_id
 * @property int|null $target_id
 * @property string $message
 * @property array $context
 * @property int|null $color
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $full_timestamp
 * @property-read mixed $is_own_message
 * @property-read mixed $name
 * @property-read mixed $notify
 * @property-read mixed $receiver
 * @property-read mixed $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message channel(\App\Channel $channel, $includeNull = true)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Message onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message public()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message target(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Message withoutTrashed()
 */
	class Message extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Emote
 *
 * @property int $id
 * @property string $type
 * @property int|null $channel_id
 * @property int|null $user_id
 * @property int|null $target_id
 * @property string $message
 * @property array $context
 * @property int|null $color
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Channel|null $channel
 * @property-read mixed $full_timestamp
 * @property-read mixed $is_own_message
 * @property-read mixed $name
 * @property-read mixed $notify
 * @property-read mixed $receiver
 * @property-read mixed $timestamp
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message channel(\App\Channel $channel, $includeNull = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message public()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message target(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Emote whereUserId($value)
 */
	class Emote extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Info
 *
 * @property int $id
 * @property string $type
 * @property int|null $channel_id
 * @property int|null $user_id
 * @property int|null $target_id
 * @property string $message
 * @property array $context
 * @property int|null $color
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Channel|null $channel
 * @property-read mixed $full_timestamp
 * @property-read mixed $is_own_message
 * @property-read mixed $name
 * @property-read mixed $notify
 * @property-read mixed $receiver
 * @property-read mixed $timestamp
 * @property-read \App\User|null $target
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message channel(\App\Channel $channel, $includeNull = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message public()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message target(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Info whereUserId($value)
 */
	class Info extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Post
 *
 * @property int $id
 * @property string $type
 * @property int|null $channel_id
 * @property int|null $user_id
 * @property int|null $target_id
 * @property string $message
 * @property array $context
 * @property int|null $color
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Channel|null $channel
 * @property-read mixed $full_timestamp
 * @property-read mixed $is_own_message
 * @property-read mixed $name
 * @property-read mixed $notify
 * @property-read mixed $receiver
 * @property-read mixed $timestamp
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message channel(\App\Channel $channel, $includeNull = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message public()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message target(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Post whereUserId($value)
 */
	class Post extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\System
 *
 * @property int $id
 * @property string $type
 * @property int|null $channel_id
 * @property int|null $user_id
 * @property int|null $target_id
 * @property string $message
 * @property array $context
 * @property int|null $color
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Channel|null $channel
 * @property-read mixed $full_timestamp
 * @property-read mixed $is_own_message
 * @property-read mixed $name
 * @property-read mixed $notify
 * @property-read mixed $receiver
 * @property-read mixed $timestamp
 * @property-read \App\User|null $target
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message channel(\App\Channel $channel, $includeNull = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message public()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message target(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\System whereUserId($value)
 */
	class System extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Whisper
 *
 * @property int $id
 * @property string $type
 * @property int|null $channel_id
 * @property int|null $user_id
 * @property int|null $target_id
 * @property string $message
 * @property array $context
 * @property int|null $color
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $full_timestamp
 * @property-read mixed $is_own_message
 * @property-read mixed $name
 * @property-read mixed $notify
 * @property-read mixed $receiver
 * @property-read mixed $timestamp
 * @property-read mixed $whisper_direction
 * @property-read \App\User|null $target
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message channel(\App\Channel $channel, $includeNull = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message public()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message target(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message\Whisper whereUserId($value)
 */
	class Whisper extends \Eloquent {}
}

namespace App{
/**
 * App\Online
 *
 * @property int $id
 * @property int $channel_id
 * @property int $login_id
 * @property string $status
 * @property-read \App\Channel $channel
 * @property-read \App\Login $login
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Online whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Online whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Online whereLoginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Online whereStatus($value)
 */
	class Online extends \Eloquent {}
}

namespace App{
/**
 * App\Role
 *
 * @property int $id
 * @property string $title
 * @property string $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereTitle($value)
 */
	class Role extends \Eloquent {}
}

namespace App{
/**
 * App\Settings
 *
 * @property int $user_id
 * @property string $channels
 * @property string $highlight
 * @property int $maxMessages
 * @property int|null $interval
 * @property string $timezone
 * @property string $theme
 * @property array $notify
 * @property-read \App\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereChannels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereHighlight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereMaxMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereUserId($value)
 */
	class Settings extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $role
 * @property int $tier
 * @property string $public_key
 * @property string $private_key
 * @property int $is_active
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $discouraged
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ban[] $bans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Channel[] $channels
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Conversation[] $conversationPosts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Conversation[] $conversations
 * @property-read mixed $ban
 * @property-read mixed $ban_totals
 * @property-read mixed $expired_bans
 * @property-read mixed $joined
 * @property mixed $public_role
 * @property-read mixed $real_name
 * @property-read mixed $role_num
 * @property-read mixed $seen
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ignore[] $ignores
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Login[] $logins
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $protectors
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $protegees
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Role[] $publicRoles
 * @property-read \App\Settings $settings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Vouch[] $vouches
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User inactive()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDiscouraged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereTier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\Vouch
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $protegee_id
 * @property string $email
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $protector
 * @property-read \App\User|null $protegee
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vouch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vouch whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vouch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vouch whereProtegeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vouch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vouch whereUserId($value)
 */
	class Vouch extends \Eloquent {}
}

