<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class VerifyAccount
{
    protected $except = [
        'account',
        'account/logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!is_null($user = Auth::user())) {
            if (!$user->is_active || $user->isBanned()) {
                if (!in_array($request->path(), $this->except)) {
                    if ($request->ajax()) {
                        return response('Banned.', 307);
                    }

                    return redirect('account');
                }
            }
        }

        return $next($request);
    }
}
