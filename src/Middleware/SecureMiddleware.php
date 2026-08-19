<?php

namespace Laracroft\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\TokenMismatchException;
use \Closure;

class SecureMiddleware extends PreventRequestForgery
{
    /**
     * @inheritdoc
     */
    protected $except = [
        'api/*/client/*',
    ];

    /**
     * @inheritdoc
     */
    public function handle($request, Closure $next)
    {
        if (
            $this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->hasValidOrigin($request) ||
            $this->tokensMatch($request)
        ) {
            return tap($next($request), function ($response) use ($request) {
                if ( $this->shouldAddXsrfTokenCookie() ) {
                    $this->addCookieToResponse($request, $response);
                }
            });
        }

        throw new TokenMismatchException(__('global.api.invalid-csrf-token'), 419);
    }
}
