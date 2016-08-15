<?php


namespace App\CMS\Data;

use App\Ban;
use App\Login;
use Siipis\CMS\Data\DataProvider;
use App\Vouch;
use Access;
use App\User;
use Auth;

class Count extends DataProvider
{
    /**
     * @inheritDoc
     */
    public function dataAll()
    {
        $data = [];

        if (Auth::check()) {
            $data['vouches'] = Vouch::where('user_id', Auth::id())->count();

            if (Access::can('control.registration')) {
                $data['pending'] = User::where('is_active', false)->count();
            }

            if (Access::can('control.bans')) {
                $data['banned'] = Ban::active()->count();
            }

            if (Access::can('control.users') || Access::can('view.users')) {
                $data['users'] = User::all()->count() - 1;
            }
            
            return $data;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function dataOne($id)
    {
        return null;
    }
    
    /**
     * @return int
     */
    public function dataOnline()
    {
        return Login::online()->count();
    }

    /**
     * @inheritDoc
     */
    public function getAccessor()
    {
        return 'count';
    }

}