<?php

namespace Laracroft\Helpers;

class Tokenizer
{
    /**
     * @inheritdoc
     */
    private static function resolveSalt() : string
    {
        $salt = config('app.salt');
        if ( empty($salt) ) {
            throw new \Exception('APP SALT is not configured');
        }

        if ( str_starts_with(strtolower((string)$salt), 'base64:') ) {
            $salt = substr((string)$salt, 7);
            $decoded = base64_decode($salt, true);
            if ( $decoded === false || strlen($decoded) !== 32 ) {
                throw new \Exception('APP SALT must decode to a valid 32-byte key');
            }
            return $decoded;
        }

        if ( strlen((string)$salt) !== 32 ) {
            throw new \Exception('APP SALT must be exactly 32 characters long');
        }

        return (string)$salt;
    }

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

        $salt = self::resolveSalt();

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

        $salt = self::resolveSalt();

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
