<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $visible = [
        'title', 'icon'
    ];

    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
