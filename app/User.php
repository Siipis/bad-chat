<?php

namespace App;

use App\Traits\HasRoles;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mail;

class User extends Authenticatable
{
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'public_key', 'private_key', 'tier'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'name', 'role', 'publicRole', 'status', 'ignored'
    ];

    protected $appends = [
        'publicRole',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Eloquent model configuration
    |
    */

    public function settings()
    {
        return $this->hasOne('App\Settings');
    }

    public function protegees()
    {
        return $this->belongsToMany('App\User', 'vouches', 'user_id', 'protegee_id');
    }

    public function protectors()
    {
        return $this->belongsToMany('App\User', 'vouches', 'protegee_id', 'user_id');
    }

    public function vouches()
    {
        return $this->hasMany('App\Vouch');
    }

    public function bans()
    {
        return $this->hasMany('App\Ban');
    }

    public function channels()
    {
        return $this->hasMany('App\Channel');
    }

    public function ignores()
    {
        return $this->hasMany('App\Ignore');
    }

    public function publicRoles()
    {
        return $this->belongsToMany('App\Role');
    }

    public function logins()
    {
        return $this->hasMany('App\Login');
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    | Setters and getters
    |
    */

    public function getNameAttribute($name)
    {
        return $name == 'theQueen' ? 'the Chat Goddess' : $name;
    }

    public function getRealNameAttribute()
    {
        return $this->attributes['name'];
    }

    public function getJoinedAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getSeenAttribute()
    {
        return $this->logins->last()->updated_at->diffForHumans();
    }

    public function getTierAttribute($tier)
    {
        return $tier > $this->ranks['suspended'] ? $tier : '--';
    }

    public function getBanAttribute()
    {
        return Ban::target($this)->active()->first();
    }

    public function getExpiredBansAttribute()
    {
        return Ban::target($this)->expired()->first();
    }

    public function getBanTotalsAttribute()
    {
        $now = Carbon::now();
        $timer = Carbon::now();

        foreach ($this->bans as $ban) {
            $diff = $ban->expires->diffInHours($ban->created_at);

            $timer->subHours($diff);
        }

        $string = '';

        if ($timer < $now) {
            $days = $timer->diffInDays($now);
            $hours = $timer->addDays($days)->diffInHours($now);

            $string = $days > 0 ? "$days days, $hours hours" : "$hours hours";
        }

        return $string;
    }

    public function getPublicRoleAttribute()
    {
        return Role::leftJoin('role_user', 'role_user.role_id', '=', 'roles.id')
            ->where('user_id', $this->id)
            ->where('is_active', true)
            ->first();
    }

    public function setPublicRoleAttribute(Role $role = null)
    {
        \DB::table('role_user')->where('user_id', $this->id)->where('is_active', true)->update(['is_active' => false]);

        if ($role instanceof Role) {
            \DB::table('role_user')->where('user_id', $this->id)->where('role_id', $role->id)->update(['is_active' => true]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    |
    | Custom query scopes
    |
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }


    /*
    |--------------------------------------------------------------------------
    | Static queries
    |--------------------------------------------------------------------------
    |
    | Custom find queries
    |
    */

    /**
     * @param $name
     * @return User|null
     */
    public static function findByName($name)
    {
        return static::where('name', $name)->where('id', '!=', 1)->first();
    }

    /**
     * @param $name
     * @return User
     */
    public static function findByNameOrFail($name)
    {
        return static::where('name', $name)->where('id', '!=', 1)->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper methods
    |--------------------------------------------------------------------------
    |
    | Various helper methods
    |
    */

    public function activate($silent = false)
    {
        $this->is_active = true;
        $this->save();

        if (!$silent) {
            $this->sendActivation();
        }
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }

    public function promote(User $promoter)
    {
        if ($this->attributes['role'] == $this->ranks['admin'] || $this->attributes['role'] >= $promoter->attributes['role']) {
            return false;
        }

        $this->attributes['role'] ++;

        $this->save();

        return true;
    }

    public function demote(User $demoter)
    {
        if ($this->attributes['role'] == $this->ranks['member'] || $this->attributes['role'] > $demoter->attributes['role']) {
            return false;
        }

        $this->attributes['role'] --;

        $this->save();

        return true;
    }

    public function updateTier($forceCascade = false, $sendEmail = true)
    {
        // Don't update the root user
        if ($this->id == 1) {
            return;
        }

        $highestVouch = Vouch::where('vouches.email', $this->email)
            ->where('vouches.user_id', '!=', $this->id)
            ->where('users.tier', '>', $this->ranks['suspended'])
            ->leftJoin('users', 'users.id', '=', 'vouches.user_id')
            ->orderBy('users.tier')
            ->first();

        $oldTier = isset($this->attributes['tier']) ? $this->attributes['tier'] : $this->ranks['suspended'];

        if (is_null($highestVouch) || !$highestVouch->protector->canVouch()) {
            $this->role = 'member';
            $this->tier = $this->ranks['suspended'];

            if ($oldTier > $this->ranks['suspended'] && $sendEmail) {
                $this->sendUninvitation();
            }
        } else {
            if ($oldTier == $this->ranks['suspended'] && $sendEmail) {
                $this->sendReinvitation();
            }

            $this->tier = $highestVouch->protector->tier + 1;
        }

        $this->save();

        if ($this->attributes['tier'] != $oldTier || $forceCascade) {
            foreach ($this->protegees as $protegee) {
                $protegee->updateTier($forceCascade);
            }
        }
    }

    public function canVouch()
    {
        return $this->tier <= config('chat.vouching.maxTier') && !$this->isSuspended();
    }

    public function isSuspended()
    {
        return $this->attributes['tier'] == $this->ranks['suspended'];
    }

    public function isBanned()
    {
        return Ban::target($this)->active()->count() > 0;
    }

    public function hasVouched(User $user)
    {
        return $this->vouches->where('protegee_id', $user->id)->count() > 0;
    }

    public function isStaff()
    {
        return $this->attributes['role'] > $this->ranks['member'];
    }

    public function isAdmin()
    {
        return $this->attributes['role'] == $this->ranks['admin'];
    }

    public function hasPublicRole(Role $role = null)
    {
        if(is_null($role)) {
            return \DB::table('role_user')->where('user_id', $this->id)->count() > 0;
        }

        return \DB::table('role_user')->where('user_id', $this->id)->where('role_id', $role->id)->count() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Mail
    |--------------------------------------------------------------------------
    |
    | User specific email messages
    |
    */

    private function sendActivation()
    {
        $email = $this->email;
        $name = $this->name;
        $app = config('chat.name');
        $login_link = config('chat.login.url');
        $account_link = action('AccountController@getIndex');

        Mail::send('emails.activated', [
            'user' => $name,
            'app' => $app,
            'login_link' => $login_link,
            'account_link' => $account_link,
        ], function ($m) use ($email, $app) {
            $m->replyTo('no-reply@varjohovi.net');
            $m->subject("Your $app account has been activated");

            $m->to($email);
        });
    }

    private function sendUninvitation()
    {
        $email = $this->email;
        $name = $this->name;
        $app = config('chat.name');

        Mail::send('emails.uninvite', [
            'user' => $name,
            'app' => $app,
        ], function ($m) use ($email, $app) {
            $m->replyTo('no-reply@varjohovi.net');
            $m->subject("Your $app account has been suspended");

            $m->to($email);
        });
    }

    private function sendReinvitation()
    {
        $email = $this->email;
        $name = $this->name;
        $app = config('chat.name');

        Mail::send('emails.reinvite', [
            'user' => $name,
            'app' => $app,
            'link' => config('chat.login.url'),
        ], function ($m) use ($email, $app) {
            $m->replyTo('no-reply@varjohovi.net');
            $m->subject("Welcome back to $app!");

            $m->to($email);
        });
    }

    public function sendEmail($title, $text)
    {
        $email = $this->email;
        $app = config('chat.name');

        $format = '';
        $paragraphs = preg_split("/\\r\\n|\\r|\\n/", trim($text));

        foreach ($paragraphs as $p) {
            if (empty($p)) {
                continue;
            }

            $format .= "<p>$p</p>\n";
        }

        Mail::send('emails.general', [
            'text' => $format
        ], function ($m) use ($title, $email, $app) {
            $m->replyTo('no-reply@varjohovi.net');
            $m->subject("[$app] $title");

            $m->to($email);
        });
    }
}
