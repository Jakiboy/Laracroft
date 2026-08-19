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

        // Check if already encrypted (only when prefix is expected)
        if ( $prefix && self::isEncrypted($data) ) {
            return $data;
        }

        $salt = config('app.salt');
        if ( empty($salt) ) {
            throw new \Exception('APP SALT is not configured');
        }

        // Decode salt if it's base64 encoded
        $salt = base64_decode(str_replace('base64:', '', $salt));

        // Create a unique initialization vector
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

        // Encrypt the data
        $encrypted = openssl_encrypt($data, $cipher, $salt, 0, $iv);

        // Combine IV and encrypted data, then encode
        $result = base64_encode($iv . $encrypted);

        // Add prefix to identify encrypted data if requested
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

        // Check if data is encrypted (only when prefix is expected)
        if ( $prefix && !self::isEncrypted($data) ) {
            return $data;
        }

        $salt = config('app.salt');
        if ( empty($salt) ) {
            throw new \Exception('APP_SALT is not configured');
        }

        // Decode salt if it's base64 encoded
        $salt = base64_decode(str_replace('base64:', '', $salt));

        // Remove prefix if expected
        if ( $prefix ) {
            $data = str_replace('[APP]', '', $data);
        }

        // Decode the data
        $decoded = base64_decode($data);

        // Extract IV and encrypted data
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        // Decrypt the data
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
