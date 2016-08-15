<?php

namespace App\Http\Controllers;

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
        return CMS::render(Auth::check() ? 'chat.main' : 'auth.login', [
            'app' => config('chat.name'),
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

        return redirect('/');
    }
}
