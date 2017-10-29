<?php
/**
 * An helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Ban
 *
 * @property integer $id
 * @property integer $user_id
 * @property \Carbon\Carbon $expires
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\User $user
 * @property-read mixed $until
 * @method static \Illuminate\Database\Query\Builder|\App\Ban whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban whereExpires($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban target($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Ban active()
 * @method static \Illuminate\Database\Query\Builder|\App\Ban expired()
 */
	class Ban extends \Eloquent {}
}

namespace App{
/**
 * App\Bounce
 *
 * @property integer $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $ip
 * @method static \Illuminate\Database\Query\Builder|\App\Bounce whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bounce whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bounce whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bounce whereIp($value)
 */
	class Bounce extends \Eloquent {}
}

namespace App{
/**
 * App\Channel
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $topic
 * @property string $access
 * @property string $expires
 * @property boolean $isDefault
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property integer $slowed
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Online[] $online
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Message[] $messages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Invite[] $invites
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereTopic($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereAccess($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereExpires($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereIsDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel whereSlowed($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Channel defaults()
 * @method static \Illuminate\Database\Query\Builder|\App\Channel expired()
 */
	class Channel extends \Eloquent {}
}

namespace App{
/**
 * App\Conversation
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $user_id
 * @property string $title
 * @property string $message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $participants
 * @property-read \App\Conversation $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Conversation[] $responses
 * @property-read mixed $names
 * @property-read mixed $read_at
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation threads()
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation parent($conversation)
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation visible()
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation trashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Conversation readable()
 */
	class Conversation extends \Eloquent {}
}

namespace App{
/**
 * App\Event
 *
 * @property integer $id
 * @property string $level
 * @property integer $code
 * @property string $event
 * @property string $context
 * @property string $url
 * @property string $ip
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereLevel($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereEvent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereContext($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereUpdatedAt($value)
 */
	class Event extends \Eloquent {}
}

namespace App{
/**
 * App\Ignore
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $target_id
 * @property-read \App\User $user
 * @property-read \App\User $target
 * @method static \Illuminate\Database\Query\Builder|\App\Ignore whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ignore whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ignore whereTargetId($value)
 */
	class Ignore extends \Eloquent {}
}

namespace App{
/**
 * App\Invite
 *
 * @property integer $id
 * @property integer $channel_id
 * @property integer $user_id
 * @property integer $target_id
 * @property boolean $role
 * @property-read \App\Channel $channel
 * @property-read \App\User $user
 * @property-read \App\User $target
 * @property-read mixed $role_num
 * @method static \Illuminate\Database\Query\Builder|\App\Invite whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Invite whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Invite whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Invite whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Invite whereRole($value)
 */
	class Invite extends \Eloquent {}
}

namespace App{
/**
 * App\Login
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $ip
 * @property string $agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $logout_at
 * @property boolean $closed
 * @property string $key
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Channel[] $channels
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Online[] $onlines
 * @property-read mixed $timestamp
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereAgent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereLogoutAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereClosed($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login whereKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Login online()
 * @method static \Illuminate\Database\Query\Builder|\App\Login userId($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Login expired()
 */
	class Login extends \Eloquent {}
}

namespace App{
/**
 * App\Message
 *
 * @property integer $id
 * @property string $type
 * @property integer $channel_id
 * @property integer $user_id
 * @property integer $target_id
 * @property string $message
 * @property string $context
 * @property integer $color
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read mixed $timestamp
 * @property-read mixed $full_timestamp
 * @property-read mixed $name
 * @property-read mixed $receiver
 * @property-read mixed $is_own_message
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereContext($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereColor($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message channel($channel, $includeNull = true)
 * @method static \Illuminate\Database\Query\Builder|\App\Message target($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Message public()
 */
	class Message extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Emote
 *
 * @property integer $id
 * @property string $type
 * @property integer $channel_id
 * @property integer $user_id
 * @property integer $target_id
 * @property string $message
 * @property string $context
 * @property integer $color
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read mixed $timestamp
 * @property-read mixed $full_timestamp
 * @property-read mixed $name
 * @property-read mixed $receiver
 * @property-read mixed $is_own_message
 * @property-read \App\Channel $channel
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereContext($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereColor($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Emote whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message channel($channel, $includeNull = true)
 * @method static \Illuminate\Database\Query\Builder|\App\Message target($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Message public()
 */
	class Emote extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Info
 *
 * @property integer $id
 * @property string $type
 * @property integer $channel_id
 * @property integer $user_id
 * @property integer $target_id
 * @property string $message
 * @property string $context
 * @property integer $color
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read mixed $name
 * @property-read mixed $timestamp
 * @property-read mixed $full_timestamp
 * @property-read mixed $receiver
 * @property-read mixed $is_own_message
 * @property-read \App\Channel $channel
 * @property-read \App\User $user
 * @property-read \App\User $target
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereContext($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereColor($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Info whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message channel($channel, $includeNull = true)
 * @method static \Illuminate\Database\Query\Builder|\App\Message target($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Message public()
 */
	class Info extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Post
 *
 * @property integer $id
 * @property string $type
 * @property integer $channel_id
 * @property integer $user_id
 * @property integer $target_id
 * @property string $message
 * @property string $context
 * @property integer $color
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read mixed $timestamp
 * @property-read mixed $full_timestamp
 * @property-read mixed $name
 * @property-read mixed $receiver
 * @property-read mixed $is_own_message
 * @property-read \App\Channel $channel
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereContext($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereColor($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Post whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message channel($channel, $includeNull = true)
 * @method static \Illuminate\Database\Query\Builder|\App\Message target($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Message public()
 */
	class Post extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\System
 *
 * @property integer $id
 * @property string $type
 * @property integer $channel_id
 * @property integer $user_id
 * @property integer $target_id
 * @property string $message
 * @property string $context
 * @property integer $color
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read mixed $name
 * @property-read mixed $timestamp
 * @property-read mixed $full_timestamp
 * @property-read mixed $receiver
 * @property-read mixed $is_own_message
 * @property-read \App\Channel $channel
 * @property-read \App\User $user
 * @property-read \App\User $target
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereContext($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereColor($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\System whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message channel($channel, $includeNull = true)
 * @method static \Illuminate\Database\Query\Builder|\App\Message target($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Message public()
 */
	class System extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * App\Models\Message\Whisper
 *
 * @property integer $id
 * @property string $type
 * @property integer $channel_id
 * @property integer $user_id
 * @property integer $target_id
 * @property string $message
 * @property string $context
 * @property integer $color
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read mixed $whisper_direction
 * @property-read mixed $timestamp
 * @property-read mixed $full_timestamp
 * @property-read mixed $name
 * @property-read mixed $receiver
 * @property-read mixed $is_own_message
 * @property-read \App\User $user
 * @property-read \App\User $target
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereContext($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereColor($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message\Whisper whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Message channel($channel, $includeNull = true)
 * @method static \Illuminate\Database\Query\Builder|\App\Message target($user)
 * @method static \Illuminate\Database\Query\Builder|\App\Message public()
 */
	class Whisper extends \Eloquent {}
}

namespace App{
/**
 * App\Online
 *
 * @property integer $id
 * @property integer $channel_id
 * @property integer $login_id
 * @property string $status
 * @property-read \App\Login $login
 * @property-read \App\Channel $channel
 * @method static \Illuminate\Database\Query\Builder|\App\Online whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Online whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Online whereLoginId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Online whereStatus($value)
 */
	class Online extends \Eloquent {}
}

namespace App{
/**
 * App\Role
 *
 * @property integer $id
 * @property string $title
 * @property string $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereIcon($value)
 */
	class Role extends \Eloquent {}
}

namespace App{
/**
 * App\Settings
 *
 * @property integer $user_id
 * @property string $channels
 * @property string $highlight
 * @property integer $maxMessages
 * @property integer $interval
 * @property string $timezone
 * @property string $theme
 * @property-read \App\User $owner
 * @method static \Illuminate\Database\Query\Builder|\App\Settings whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Settings whereChannels($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Settings whereHighlight($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Settings whereMaxMessages($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Settings whereInterval($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Settings whereTimezone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Settings whereTheme($value)
 */
	class Settings extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property boolean $role
 * @property boolean $tier
 * @property string $public_key
 * @property string $private_key
 * @property boolean $is_active
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property boolean $discouraged
 * @property-read \App\Settings $settings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $protegees
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $protectors
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Vouch[] $vouches
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ban[] $bans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Channel[] $channels
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ignore[] $ignores
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Role[] $publicRoles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Login[] $logins
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Conversation[] $conversationPosts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Conversation[] $conversations
 * @property-read mixed $real_name
 * @property-read mixed $joined
 * @property-read mixed $seen
 * @property-read mixed $ban
 * @property-read mixed $expired_bans
 * @property-read mixed $ban_totals
 * @property mixed $public_role
 * @property-read mixed $role_num
 * @method static \Illuminate\Database\Query\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereRole($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereTier($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePublicKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePrivateKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereIsActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereDiscouraged($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User active()
 * @method static \Illuminate\Database\Query\Builder|\App\User inactive()
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\Vouch
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $protegee_id
 * @property string $email
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $protector
 * @property-read \App\User $protegee
 * @method static \Illuminate\Database\Query\Builder|\App\Vouch whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Vouch whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Vouch whereProtegeeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Vouch whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Vouch whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Vouch whereUpdatedAt($value)
 */
	class Vouch extends \Eloquent {}
}

