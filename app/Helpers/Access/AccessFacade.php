<?php


namespace App\Helpers\Access;


use Illuminate\Support\Facades\Facade;

class AccessFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'access';
    }

}