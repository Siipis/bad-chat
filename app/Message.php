<?php

namespace App;

use App\Settings;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'message', 'context', 'color'];

    protected $require = [];

    protected $visible = [
        'id', 'timestamp', 'type', 'name', 'receiver', 'message', 'color', 'whisperDirection', 'isOwnMessage'
    ];

    protected $appends = [
        'timestamp', 'name', 'receiver', 'isOwnMessage'
    ];

    protected $casts = [
        'context' => 'array'
    ];

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    |
    | Custom query scopes
    |
    */

    public function scopeChannel($query, Channel $channel, $includeNull = true)
    {
        if ($includeNull) {
            return $query->whereNull('channel_id')->orWhere('channel_id', $channel->id);
        }

        return $query->where('channel_id', $channel->id);
    }

    public function scopeTarget($query, User $user)
    {
        return $query->where('target_id', $user->id)->orWhereNull('target_id')->orWhere('user_id', $user->id);
    }

    public function scopePublic($query)
    {
        return $query->whereNull('target_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    | Mutators and accessors
    |
    */

    public function getTimestampAttribute()
    {
        if (Auth::check()) {
            $settings = Settings::user(Auth::user());

            $this->created_at = $this->created_at->timezone($settings->timezone);
        }

        return $this->created_at->toTimeString();
    }

    public function getFullTimestampAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getNameAttribute()
    {
        return is_null($this->user) ? null : $this->user->name;
    }

    public function getReceiverAttribute()
    {
        return is_null($this->target) ? null : $this->target->name;
    }

    public function getIsOwnMessageAttribute() {
        return !is_null($this->user) && $this->user->id == Auth::id();
    }

    /*
    |--------------------------------------------------------------------------
    | Override
    |--------------------------------------------------------------------------
    |
    | Change default model behaviour
    |
    */

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (isset($this->require)) {
            foreach ($this->require as $key) {
                if (!isset($this->attributes[$key]) || empty($this->attributes[$key])) {
                    throw new \Exception("Model constraint error: Attribute [$key] cannot be empty.");
                }
            }
        }

        return parent::save($options);
    }


    /**
     * Get all of the models from the database.
     *
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function all($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $instance = new static;

        $collection = $instance->newQuery()->get($columns);

        foreach ($collection as $key => $message) {
            $collection[$key] = self::toModel($message);
        }

        return $collection;
    }

    /**
     * Returns all records after a given date
     *
     * @param Carbon $timestamp
     * @param Channel $channel
     * @return Collection
     */
    public static function since(Carbon $timestamp, Channel $channel)
    {
        $instance = new static;

        $collection = $instance->channel($channel)->target(\Auth::user())->where('created_at', '>', $timestamp)->get();

        foreach ($collection as $key => $message) {
            $collection[$key] = $message->toModel();
        }

        return $collection;
    }

    /**
     * Returns true if messages exist after the given date
     *
     * @param Carbon $timestamp
     * @param Channel $channel
     * @return bool
     */
    public static function existsSince(Carbon $timestamp, Channel $channel)
    {
        $instance = new static;

        return $instance->channel($channel)->target(\Auth::user())->where('created_at', '>', $timestamp)->count() > 0;
    }

    /**
     * Returns all records after a given ID
     *
     * @param int $id
     * @param Channel $channel
     * @return mixed
     */
    public static function after($id, Channel $channel)
    {
        $instance = new static;

        $collection = $instance->channel($channel)->target(\Auth::user())->where('id', '>', $id)->get();

        foreach ($collection as $key => $message) {
            $collection[$key] = $message->toModel();
        }

        return $collection;
    }

    /**
     * Returns true if messages exist after a given ID
     *
     * @param int $id
     * @param Channel $channel
     * @return bool
     */
    public static function existsAfter($id, Channel $channel, $allowDuplicates = true)
    {
        $instance = new static;

        return $instance->channel($channel, $allowDuplicates)->target(\Auth::user())->where('id', '>', $id)->count() > 0;
    }

    /**
     * Returns a certain number of records
     *
     * @param Channel $channel
     * @param int $take
     * @return mixed
     */
    public static function latest(Channel $channel, $take = 0)
    {
        $id = static::latestId($channel, $take);

        return static::after($id, $channel);
    }

    /**
     * Returns a the last id of a certain number of records
     *
     * @param Channel $channel
     * @param int $take
     * @return mixed
     */
    public static function latestId(Channel $channel, $take = 0)
    {
        $instance = new static;

        $latest = $instance->channel($channel)->target(\Auth::user())->orderBy('id', 'desc');

        if ($take > 0) {
            $latest = $latest->take($take + 1)->get()->last();
        } else {
            $latest = $latest->first();
        }

        return !is_null($latest) ? $latest->id : 0;
    }

    /**
     * Converts a message to a specific type model, e.g. Post
     *
     * @param Message $message
     * @return mixed
     * @throws \Exception
     */
    public function toModel(Message $message = null) {
        if (is_null($message)) {
            $message = $this;
        }

        try {
            $class = '\App\Models\Message\\'. ucfirst($message->type);

            Model::unguard();

            $model = new $class();

            $model->setRawAttributes($message->getAttributes());

            Model::reguard();

            return $model;
        } catch (FatalThrowableError $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
