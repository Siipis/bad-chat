<?php


namespace App\Traits;


trait ChannelMessage
{
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Model relationships
    |
    */

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

}