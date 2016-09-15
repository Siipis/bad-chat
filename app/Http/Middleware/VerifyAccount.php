<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Login;
use App\Models\Message\System;

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
            if (!$user->is_active || $user->isBanned() || $user->isSuspended()) {
                // Logs the user out from channels

                if (!is_null($login = Login::active($user))) {
                    $message = $user->isSuspended() ? 'suspended' : 'banned';

                    foreach ($login->channels as $channel) {
                        $channel->messages()->save(new System([
                            'message' => $message,
                            'context' => [
                                'user' => $user->name,
                            ]
                        ]));
                    }

                    foreach ($login->onlines as $online) {
                        $online->delete();
                    }
                }

                \Log::debug('Account verification failed.', [
                    'IP' => $request->ip(),
                    'Auth' => Auth::user(),
                    'URL' => $request->fullUrl(),
                ]);

                // Redirects or sends a 307 response
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
