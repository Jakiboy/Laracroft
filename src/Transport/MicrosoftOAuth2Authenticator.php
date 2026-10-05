<?php

namespace Laracroft\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Transport\Smtp\Auth\AuthenticatorInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use RuntimeException;

class MicrosoftOAuth2Authenticator implements AuthenticatorInterface
{
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
        return 'XOAUTH2';
    }

    /**
     * @inheritdoc
     */
    public function authenticate(EsmtpTransport $client) : void
    {
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => $this->scope,
            ]);
        $response->throw();

        $accessToken = $response->json('access_token');
        if ( !is_string($accessToken) || $accessToken === '' ) {
            throw new RuntimeException('Microsoft OAuth2 token response did not include an access token.');
        }

        $credentials = base64_encode(
            "user={$client->getUsername()}\x01auth=Bearer {$accessToken}\x01\x01",
        );

        $client->executeCommand("AUTH XOAUTH2 {$credentials}\r\n", [235]);
    }
}
