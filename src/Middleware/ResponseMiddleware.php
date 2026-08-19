<?php

namespace Laracroft\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Closure;

class ResponseMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request, Closure $next) : Response
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
