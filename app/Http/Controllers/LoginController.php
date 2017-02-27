<?php

namespace App\Http\Controllers;

use App\Bounce;
use App\Login;
use App\Models\Message\System;
use App\User;
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

        $lastVisit = Bounce::previous()->created_at->diffForHumans();

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
