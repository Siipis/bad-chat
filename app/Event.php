<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['level', 'code', 'event', 'context', 'url', 'ip'];

    protected $hidden = ['ip'];

    protected $casts = [
        'context' => 'array',
    ];
}
