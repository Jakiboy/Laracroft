<?php

namespace Laracroft\Transport;

use Laracroft\Exceptions\TransportException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Transport\Smtp\Auth\AuthenticatorInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MicrosoftOAuth2Authenticator implements AuthenticatorInterface
{
    protected const string TOKEN_URL    = 'https://login.microsoftonline.com/%s/oauth2/v2.0/token';
    protected const string GRANT_TYPE   = 'client_credentials';
    protected const string AUTH_KEYWORD = 'XOAUTH2';

    protected int $timeout = 15;

    /**
     * @inheritdoc
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $scope,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getAuthKeyword() : string
    {
        return self::AUTH_KEYWORD;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(EsmtpTransport $client) : void
    {
        $response = Http::asForm()
            ->acceptJson()
            ->timeout($this->timeout)
            ->post(sprintf(self::TOKEN_URL, $this->tenantId), [
                'grant_type'    => self::GRANT_TYPE,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => $this->scope,
            ]);

        $response->throw();
        $accessToken = $response->json('access_token');

        if ( !is_string($accessToken) || $accessToken === '' ) {
            throw new TransportException('Microsoft OAuth2 token response did not include an access token.');
        }

        $credentials = $this->getCredentials($accessToken, $client->getUsername());
        $client->executeCommand($this->getCommand($credentials), [235]);
    }

    /**
     * @inheritdoc
     */
    protected function getCredentials(string $accessToken, string $username) : string
    {
        return base64_encode(
            "user={$username}\x01auth=Bearer {$accessToken}\x01\x01",
        );
    }

    /**
     * @inheritdoc
     */
    protected function getCommand(string $credentials) : string
    {
        return "AUTH " . self::AUTH_KEYWORD . " {$credentials}\r\n";
    }
}
