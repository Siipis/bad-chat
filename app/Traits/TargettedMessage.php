<?php


namespace App\Traits;


trait TargettedMessage
{
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Model relationships
    |
    */

    public function target()
    {
        return $this->belongsTo('App\User', 'target_id');
    }

}