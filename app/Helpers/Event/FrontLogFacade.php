<?php


namespace App\Helpers\Event;


use Illuminate\Support\Facades\Facade;

class FrontLogFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'frontLog';
    }
}