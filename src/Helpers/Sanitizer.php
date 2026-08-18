<?php

namespace Laracroft\Helpers;

use DateTimeInterface;

class Sanitizer
{
    /**
     * Sanitize name.
     *
     * @param string $name
     * @return string
     */
    public static function name(string $name) : string
    {
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Sanitize content with proper HTML purification.
     *
     * @param string $content
     * @param array|string $allowed
     * @return string
     */
    public static function content(string $content, array|string|null $allowed = null, int $limit = 6000) : string
    {
        if ( $allowed === null ) {
            $allowed = [
                '<p>',
                '<br>',
                '<h1>',
                '<h2>',
                '<h3>',
                '<strong>',
                '<b>',
                '<em>',
                '<i>',
                '<u>',
                '<s>',
                '<strike>',
                '<ol>',
                '<ul>',
                '<li>',
                '<a>',
                '<span>',
                '<div>'
            ];
        }

        // Null bytes and control characters
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);

        // JS/CSS Injection patterns
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
        $content = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content);
        $content = preg_replace('/javascript:/i', '', $content);
        $content = preg_replace('/on\w+\s*=/i', '', $content);
        $content = preg_replace('/vbscript:/i', '', $content);
        $content = preg_replace('/data:/i', '', $content);

        // SQL injection patterns
        $sql = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|UNION|SCRIPT|DECLARE|CAST|CONVERT|CHAR|ASCII|SUBSTRING)\b/i',
            '/--/',
            '/\/\*.*?\*\//',
            '/;/',
            '/\bOR\s+[\w\d\'"]*\s*=\s*[\w\d\'"]*\s*(--|\#|\/\*)/i',
            '/\bAND\s+[\w\d\'"]*\s*=\s*[\w\d\'"]*\s*(--|\#|\/\*)/i',
            '/\b(OR|AND)\s+[\d\'"]+\s*=\s*[\d\'"]+/i',
            '/\b(UNION|SELECT).*?(FROM|WHERE)/i',
            '/[\'"]\s*(OR|AND)\s+[\d\'"]+\s*=\s*[\d\'"]+/i',
            '/\b(exec|execute|sp_|xp_)/i',
        ];

        foreach ($sql as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // Safe tags
        $allowedTags = is_array($allowed) ? implode('', $allowed) : $allowed;
        $content = strip_tags($content, $allowedTags);

        // Limit to $limit plain-text characters
        if ( $limit > 0 && mb_strlen(strip_tags($content)) > $limit ) {
            $content = mb_substr($content, 0, $limit);
        }

        return $content;
    }

    /**
     * Sanitize email.
     *
     * @param string $email
     * @return string
     */
    public static function email(string $email) : string
    {
        // Trim whitespace
        $email = trim($email);

        // Return empty string if email is empty
        if ( empty($email) ) {
            return '';
        }

        // Sanitize and validate email
        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Validate the sanitized email
        if ( filter_var($sanitized, FILTER_VALIDATE_EMAIL) ) {
            return strtolower($sanitized);
        }

        // Return empty string if email is not valid
        return '';
    }

    /**
     * Sanitize date.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    public static function date(mixed $date, string $format = 'd/m/Y H:i:s') : string
    {
        if ( $date instanceof DateTimeInterface ) {
            $date = $date->format($format);
        }

        return (string)$date;
    }

    /**
     * Sanitize malformed UTF-8 characters.
     *
     * @param string $text
     * @return string
     */
    public static function utf8(string $text) : string
    {
        if ( empty($text) ) {
            return '';
        }

        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);

        return $text;
    }
}
