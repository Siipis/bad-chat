<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        if (
            $this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->shouldPassThrough($request) ||
            $this->tokensMatch($request)
        ) {
            return $this->addCookieToResponse($request, $next($request));
        }

        \Log::debug('CSRF token mismatch.', [
            'IP' => $request->ip(),
            'Auth' => \Auth::user(),
            'URL' => $request->fullUrl(),
        ]);

        if ($request->ajax()) {
            $request->session()->flash('request', $request->all());

            return response('Security token mismatch.', 408);
        }

        return \Redirect::back()->with([
            'alert' => [
                'type' => 'danger',
                'message' => 'Security token mismatch. Please try again!',
            ]
        ]);
    }
}
