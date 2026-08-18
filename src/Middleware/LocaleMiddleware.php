<?php

namespace Laracroft\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next) : Response
    {
        // Get language from Accept-Language header, default to 'en'
        $language = $request->header('Accept-Language', 'en');

        // Validate that it's one of the supported languages
        $supportedLanguages = ['en', 'fr'];
        if ( !in_array($language, $supportedLanguages) ) {
            $language = 'en';
        }

        // Set the application locale
        app()->setLocale($language);

        return $next($request);
    }
}
