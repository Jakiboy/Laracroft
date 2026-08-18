<?php

namespace Laracroft\Traits;

trait HasConfig
{
    /**
     * Get API version.
     */
    public static function getApiVersion() : string
    {
        return config('app.apiVersion', 'v1');
    }

    /**
     * Get App version.
     */
    public static function getAppVersion() : string
    {
        return config('app.version', '0.0.1');
    }

    /**
     * Get debug status.
     */
    public static function isDebug() : bool
    {
        return (bool)config('app.debug');
    }

    /**
     * Get dev status.
     */
    public static function isDev() : bool
    {
        return app()->environment('development', 'dev');
    }

    /**
     * Get production status.
     */
    public static function isProd() : bool
    {
        return app()->environment('production', 'prod');
    }

    /**
     * Get local status.
     */
    public static function isLocal() : bool
    {
        return app()->environment('local');
    }
}
