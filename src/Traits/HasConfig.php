<?php

namespace Laracroft\Traits;

trait HasConfig
{
    /**
     * @inheritdoc
     */
    public static function getApiVersion() : string
    {
        return config('app.apiVersion', 'v1');
    }

    /**
     * @inheritdoc
     */
    public static function getAppVersion() : string
    {
        return config('app.version', '0.0.1');
    }

    /**
     * @inheritdoc
     */
    public static function isDebug() : bool
    {
        return (bool)config('app.debug');
    }

    /**
     * @inheritdoc
     */
    public static function isDev() : bool
    {
        return app()->environment('development', 'dev');
    }

    /**
     * @inheritdoc
     */
    public static function isProd() : bool
    {
        return app()->environment('production', 'prod');
    }

    /**
     * @inheritdoc
     */
    public static function isLocal() : bool
    {
        return app()->environment('local');
    }
}
