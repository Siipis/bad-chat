<?php

namespace App\Http\Middleware;

use App\Login;
use Closure;
use Auth;
use Log;

class VerifyOnline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guest()) {
            return $this->noAccessResponse($request);
        } else {
            $user = Auth::user();

            if (!is_null($login = Login::active($user))) {
                if (!Login::verify()) {
                    return $this->noAccessResponse($request);
                }

                // Keep the login active
                $login->touch();
            } else {
                return $this->noAccessResponse($request);
            }
        }

        return $next($request);
    }

    /**
     * Return a no access response.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    private function noAccessResponse($request)
    {
        Log::debug('Unauthorized access.', [
            'IP' => $request->ip(),
            'Auth' => Auth::user(),
            'URL' => $request->fullUrl(),
        ]);

        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        }

        return redirect('/');
    }
}
