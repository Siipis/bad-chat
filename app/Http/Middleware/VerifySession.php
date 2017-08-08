<?php

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\Login;
use Closure;
use Auth;

class VerifySession
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guest()) {
            return $this->logoutResponse($request);
        } else {
            $active = Login::active();

            if (Login::verifySession()) {
                if (Login::verify()) {
                    $active->touch();
                } else {
                    if (!Login::attemptReconnect()) {
                        return $this->logoutResponse($request);
                    }
                }
            } else {
                return $this->closedResponse($request);
            }
        }

        return $next($request);
    }

    /**
     * Ask the user to relog.
     *
     * @param  Request $request
     * @return mixed
     */
    private function logoutResponse($request)
    {
        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        }

        return redirect('/');
    }

    /**
     * Refresh the page.
     *
     * @param  Request $request
     * @return mixed
     */
    private function closedResponse($request)
    {
        Login::clearSession(); // Close the session

        if ($request->ajax()) {
            return response('Connection was closed.', 307);
        }

        return redirect('/');
    }
}
