<?php

namespace App\Http\Middleware;

use Closure;
use Access;
use Auth;

class RestrictAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (!Access::can($permission)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Access Denied.', 403);
            } else if (Auth::check()) {
                abort(403);
            } else {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
