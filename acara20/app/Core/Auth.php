<?php

namespace App\Core;

use JsonException;

class Auth
{
    private const SECRET = 'kunci_rahasia_workshop_2026';

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function generateToken(array $payload): string
    {
        $header = self::base64url(json_encode(
            ['alg' => 'HS256', 'typ' => 'JWT'],
            JSON_THROW_ON_ERROR
        ));
        $payload['exp'] = time() + 300;
        $payloadEncoded = self::base64url(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = self::base64url(hash_hmac(
            'sha256',
            "$header.$payloadEncoded",
            self::SECRET,
            true
        ));

        return "$header.$payloadEncoded.$signature";
    }

    public static function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signature] = $parts;
        $headerJson = self::decodeBase64url($headerEncoded);
        $payloadJson = self::decodeBase64url($payloadEncoded);
        if ($headerJson === null || $payloadJson === null) {
            return null;
        }

        try {
            $header = json_decode($headerJson, true, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (
            !is_array($header)
            || ($header['alg'] ?? null) !== 'HS256'
            || ($header['typ'] ?? null) !== 'JWT'
            || !is_array($payload)
            || !isset($payload['exp'])
            || !is_int($payload['exp'])
        ) {
            return null;
        }

        $expectedSignature = self::base64url(hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            self::SECRET,
            true
        ));
        if (!hash_equals($expectedSignature, $signature) || $payload['exp'] <= time()) {
            return null;
        }

        return $payload;
    }

    public static function requireAuth(): array
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = '';
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0 && is_string($value)) {
                $authHeader = $value;
                break;
            }
        }
        if ($authHeader === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches) !== 1) {
            Response::json(401, false, 'Token tidak ditemukan.');
            return [];
        }

        $payload = self::validateToken($matches[1]);
        if ($payload === null) {
            Response::json(401, false, 'Token tidak valid atau kedaluwarsa.');
            return [];
        }

        return $payload;
    }

    private static function decodeBase64url(string $encoded): ?string
    {
        if ($encoded === '' || preg_match('/^[A-Za-z0-9_-]+$/', $encoded) !== 1) {
            return null;
        }

        $decoded = base64_decode(
            strtr($encoded, '-_', '+/') . str_repeat('=', (4 - strlen($encoded) % 4) % 4),
            true
        );

        return $decoded === false ? null : $decoded;
    }
}
