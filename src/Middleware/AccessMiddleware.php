<?php

namespace Laracroft\Middleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;
use \Closure;

class AccessMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request, Closure $next) : Response
    {
        if ( !config('app.adminIpRestriction.enabled') ) {
            return $next($request);
        }

        $ip = $request->ip();
        $allowedIps = config('app.adminIpRestriction.allowedIps', []);

        if ( $ip === null || !IpUtils::checkIp($ip, $allowedIps) ) {
            throw new AuthorizationException('Admin access is not permitted from this IP address.');
        }

        return $next($request);
    }
}
