<?php

namespace Laracroft\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class SecureMiddleware extends Middleware
{
    /**
     * Excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*/client/*',
    ];

    /**
     * Override default CSRF exception message from framework middleware.
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
