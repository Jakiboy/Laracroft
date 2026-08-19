<?php

namespace Laracroft\Helpers;

use Illuminate\Http\JsonResponse;

class Response extends JsonResponse
{
    public const string SUCCESS            = 'global.api.success';
    public const string ERROR              = 'global.api.error';
    public const string WARNING            = 'global.api.warning';
    public const string INFO               = 'global.api.info';
    public const string NOTFOUND           = 'global.api.not-found';
    public const string NOTEXISTING        = 'global.api.not-existing';
    public const string NOTALLOWED         = 'global.api.not-allowed';
    public const string UNAUTHENTICATED    = 'global.api.unauthenticated';
    public const string INVALIDCREDENTIALS = 'global.api.invalid-credentials';
    public const string NOTPERMITTED       = 'global.api.not-permitted';
    public const string NOTVALIDATED       = 'global.api.not-validated';
    public const string UNAVAILABLE        = 'global.api.unavailable';
    public const string TOOMANYREQUESTS    = 'global.api.rate-limit';
    public const string INVALIDCSRFTOKEN   = 'global.api.invalid-csrf-token';
    public const string NOTCONNECTED       = 'global.api.not-connected';
    public const string DBNOTCONNECTED     = 'global.app.db-not-connected';
    public const string UNEXPECTED         = 'global.app.unexpected';

    /**
     * @inheritdoc
     */
    public static function success(string $message = self::SUCCESS, $data = [], string $status = 'success', int $code = 200) : self
    {
        return self::set($message, $data, $status, $code);
    }

    /**
     * @inheritdoc
     */
    public static function error(string $message = self::ERROR, $data = [], string $status = 'error', int $code = 400) : self
    {
        return self::set($message, $data, $status, $code);
    }

    /**
     * @inheritdoc
     */
    public static function set(string $message = '', $data = [], ?string $status = null, int $code = 200) : self
    {
        $status = $status ?? ($code >= 200 && $code < 300 ? 'success' : 'error');
        $message = __($message ?: ($status === 'success' ? static::SUCCESS : static::ERROR));
        $response = [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'data'    => $data
        ];

        return new self($response, $code);
    }
}
