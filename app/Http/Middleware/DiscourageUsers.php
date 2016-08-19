<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class DiscourageUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->discouraged) {
                if ($request->ajax()) {
                    $freq = config('chat.discourage');

                    if (rand(1, $freq) == 1) {
                        return response('Session has expired.', 408);
                    }
                }
            }
        }

        return $next($request);
    }
}
