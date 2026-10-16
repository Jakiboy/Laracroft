<?php

namespace Laracroft\Middleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Closure;

class AccountMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request, Closure $next) : Response
    {
        if ( $request->user()?->deletionRequestedAt !== null ) {
            throw new AuthorizationException('Account deletion is pending review.');
        }
        return $next($request);
    }
}
