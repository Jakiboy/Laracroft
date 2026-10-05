<?php

namespace Laracroft\Providers;

use Laracroft\Transport\MicrosoftOAuth2Authenticator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use InvalidArgumentException;

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

    /**
     * @inheritdoc
     */
    public function boot() : void
    {
        Mail::extend('smtp-oauth2', function (array $config) {
            $oauth = $config['oauth'] ?? [];
            foreach (['tenant_id', 'client_id', 'client_secret', 'scope'] as $key) {
                if ( !is_string($oauth[$key] ?? null) || $oauth[$key] === '' ) {
                    throw new InvalidArgumentException('The MAIL_OAUTH_' . strtoupper($key) . ' setting is required for SMTP OAuth2.');
                }
            }

            if ( !is_string($config['username'] ?? null) || $config['username'] === '' ) {
                throw new InvalidArgumentException('The MAIL_USERNAME setting is required for SMTP OAuth2.');
            }

            $transport = new EsmtpTransport(
                $config['host'],
                (int)$config['port'],
                null,
                null,
                null,
                null,
                [
                    new MicrosoftOAuth2Authenticator(
                        $oauth['tenant_id'],
                        $oauth['client_id'],
                        $oauth['client_secret'],
                        $oauth['scope'],
                    ),
                ],
            );

            $transport->setUsername($config['username']);
            $transport->setAutoTls(true);
            $transport->setRequireTls(true);

            return $transport;

        });
    }
}