<?php

namespace Laracroft\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Closure;

class LocaleMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request, Closure $next) : Response
    {
        $language = $request->header('Accept-Language', 'en');

        $supported = ['en', 'fr'];
        if ( !in_array($language, $supported) ) {
            $language = 'en';
        }

        app()->setLocale($language);

        return $next($request);
    }
}
