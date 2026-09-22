<?php

namespace Laracroft\Helpers;

class Tokenizer
{
    /**
     * @inheritdoc
     */
    public static function encrypt(string $data, bool $prefix = true, string $cipher = 'aes-256-cbc') : string
    {
        if ( empty($data) ) {
            return '';
        }

        if ( $prefix && self::isEncrypted($data) ) {
            return $data;
        }

        $salt = config('app.salt');
        if ( empty($salt) ) {
            throw new \Exception('APP SALT is not configured');
        }

        $salt = base64_decode(str_replace('base64:', '', $salt));
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        $encrypted = openssl_encrypt($data, $cipher, $salt, 0, $iv);

        $result = base64_encode($iv . $encrypted);

        return $prefix ? '[APP]' . $result : $result;
    }

    /**
     * @inheritdoc
     */
    public static function decrypt(string $data, bool $prefix = true, string $cipher = 'aes-256-cbc') : string
    {
        if ( empty($data) ) {
            return '';
        }

        if ( $prefix && !self::isEncrypted($data) ) {
            return $data;
        }

        $salt = config('app.salt');
        if ( empty($salt) ) {
            throw new \Exception('APP_SALT is not configured');
        }

        $salt = base64_decode(str_replace('base64:', '', $salt));

        if ( $prefix ) {
            $data = str_replace('[APP]', '', $data);
        }

        $decoded = base64_decode($data);

        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        $decrypted = openssl_decrypt($encrypted, $cipher, $salt, 0, $iv);

        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * @inheritdoc
     */
    public static function isEncrypted(string $data) : bool
    {
        return str_starts_with($data, '[APP]');
    }
}
