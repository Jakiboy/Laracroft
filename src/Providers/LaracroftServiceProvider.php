<?php

namespace Laracroft\Providers;

use Illuminate\Support\ServiceProvider;

class LaracroftServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register() : void
    {
        $defaults = require __DIR__ . '/../config/activitylog.php';
        $overridePath = config_path('activitylog.php');
        $overrides = file_exists($overridePath) ? require $overridePath : [];

        config()->set('activitylog', array_merge($defaults, $overrides));
    }
}