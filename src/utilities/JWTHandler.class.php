<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/15/2020
 * Time: 12:22 PM
 */


namespace utilities;

use Config;
use exceptions\JWTException;
use exceptions\SecurityException;

/**
 * Handle retrieving JWTs from the request
 *
 * Class JWTHandler
 * @package utilities
 */
class JWTHandler
{
    // Header for all issued JWTs
    public const HEADER = array(
        'alg' => 'hs256',
        'typ' => 'jwt'
    );

    /**
     * @return string|null  JWT string, or null if not set.
     * This function does not throw any exceptions
     * @throws SecurityException
     */
    public static function currentToken(): string
    {
        if(isset($_SERVER['HTTP_JWT']))
        {
            return $_SERVER['HTTP_JWT'];
        }

        throw new SecurityException(SecurityException::MESSAGES[SecurityException::AUTHENTICATION_REQUIRED], SecurityException::AUTHENTICATION_REQUIRED);
    }

    /**
     * @param string $token
     * @return bool
     * @throws SecurityException
     */
    public static function checkAuthenticity(string $token): bool
    {
        $parts = explode('.', $token); // Explode JWT into 0 => header, 1 => payload, 2 => signature

        if(sizeof($parts) !== 3) // Not valid JWT
            return FALSE;

        // Generate signature from supplied header and payload
        $testSignature = self::generateSignature($parts[0], $parts[1]);

        // Check if signatures match
        if($testSignature === $parts[2])
            return TRUE;

        throw new SecurityException(SecurityException::MESSAGES[SecurityException::AUTHENTICATION_REQUIRED], SecurityException::AUTHENTICATION_REQUIRED);
    }

    /**
     * Get the payload from a supplied JWT
     *
     * @param string $token
     * @return array
     * @throws JWTException
     */
    public static function getPayload(string $token): array
    {
        $parts = explode('.', $token); // Explode JWT into 0 => header, 1 => payload, 2 => signature

        if(sizeof($parts) !== 3) // Not valid JWT
            throw new JWTException(JWTException::MESSAGES[JWTException::JWT_PAYLOAD_INVALID], JWTException::JWT_PAYLOAD_INVALID);

        $array = json_decode(base64_decode($parts[1]), true);

        if(!is_array($array))
            throw new JWTException(JWTException::MESSAGES[JWTException::JWT_PAYLOAD_INVALID], JWTException::JWT_PAYLOAD_INVALID);

        return $array;
    }

    public static function generateSignature(string $base64UrlHeader, string $base64UrlPayload): string
    {
        return self::base64URLEncodeString(hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, Config::OPTIONS['salt'], true));
    }

    /**
     * Make the string safe for URL sending
     *
     * @param string $str
     * @return string
     */
    public static function base64URLEncodeString(string $str): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
    }
}
