<?php

namespace App\Http\Controllers;

use App\Bounce;
use App\Login;
use App\Models\Message\System;
use App\Online;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use CMS;
use Auth;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => 'postIndex'
        ]);
    }

    public function getIndex()
    {
        if (Auth::check()) {
            if (!is_null($login = Login::active(Auth::user()))) {
                if (!Login::verify()) {
                    Login::logout();

                    return redirect('/');
                }
            }

            return CMS::render('chat.main');
        }

        Bounce::add();

        try {
            $lastVisit = Bounce::previous()->created_at->diffForHumans();
        } catch (\Exception $e) {
            // Fallback for null pointer exception
            $lastVisit = 'a long, long time ago';
        }

        return CMS::render('auth.login', [
            'online' => Login::online()->get(),
            'lastVisit' => $lastVisit,
        ]);
    }

    public function postIndex(Request $request)
    {
        $name = $request->input('name');
        $password = $request->input('password');

        if (Auth::attempt(['name' => $name, 'password' => $password, 'is_active' => true])) {
            Login::clean(false);

            Login::trace();

            return redirect('/');
        } else {
            return redirect()->back()->withErrors([
                'name' => 'Invalid username or password.',
            ]);
        }
    }

    public function getLogout()
    {
        Login::logout();

        Auth::logout();

        return redirect('/');
    }
}
