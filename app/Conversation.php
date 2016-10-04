<?php

namespace App;

use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;
use Mockery\CountValidator\Exception;

class Conversation extends Model
{
    use SoftDeletes;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function participants()
    {
        return $this->belongsToMany('App\User')->withPivot(['read_at', 'deleted_at']);
    }

    public function parent()
    {
        return $this->belongsTo('App\Conversation');
    }

    public function responses()
    {
        return $this->hasMany('App\Conversation', 'parent_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    | Attribute accessors
    |
    */

    public function getNamesAttribute()
    {
        return $this->participants->map(function($user) {
            return $user->name;
        });
    }

    public function getMessageAttribute($value)
    {
        return preg_replace("/\\r\\n|\\r|\\n/", "<br />", trim($value));
    }

    public function getReadAtAttribute()
    {
        $participation = $this->participants()->whereUserId(Auth::id())->first();

        if (is_null($participation) || is_null($participation->pivot->read_at)) {
            return null;
        }

        return new Carbon($participation->pivot->read_at);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    |
    | Custom query scopes
    |
    */

    public function scopeThreads($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeParent($query, Conversation $conversation)
    {
        return $query->where('parent_id', $conversation->id);
    }

    public function scopeVisible($query)
    {
        $pivot = $this->participants()->getTable();

        return $query->whereHas('participants', function($q) use ($pivot) {
            $q->where("$pivot.deleted_at", null)->where('user_id', Auth::id());
        });
    }

    public function scopeTrashed($query)
    {
        $pivot = $this->participants()->getTable();

        return $query->whereHas('participants', function($q) use ($pivot) {
            $q->where("$pivot.deleted_at", "!=", null)->where('user_id', Auth::id());
        });
    }

    public function scopeReadable($query)
    {
        $user = Auth::user();

        $pivot = $this->participants()->getTable();

        return $query->whereHas('participants', function($q) use ($pivot, $user) {
            $q->where("$pivot.user_id", $user->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Helper methods
    |
    */

    public function canView(User $user)
    {
        if ($this->user->id == $user->id) {
            return true;
        }

        if ($this->participants()->getQuery()->where('user_id', $user->id)->count() > 0) {
            return true;
        }

        return false;
    }

    public function isThrashed(User $user)
    {
        $participation = $this->participants()->whereUserId($user->id)->first();

        try {
            return $participation->pivot->deleted_at != null;
        } catch (Exception $e) {
            return true;
        }
    }

    public function thrash(User $user)
    {
        if ($this->canView($user)) {
            $participation = $this->participants()->whereUserId($user->id)->first();

            $participation->pivot->deleted_at = Carbon::now();
            $participation->pivot->save();

            return true;
        }

        return false;
    }

    public function read(User $user)
    {
        if ($this->canView($user)) {
            $participation = $this->participants()->whereUserId($user->id)->first();

            $participation->pivot->read_at = Carbon::now();
            $participation->pivot->save();

            return true;
        }

        return false;
    }

    public function hasUnread()
    {
        $user = Auth::user();

        if ($this->canView($user)) {
            return is_null($this->read_at) || $this->read_at->lt($this->updated_at);
        }

        return false;
    }
}
