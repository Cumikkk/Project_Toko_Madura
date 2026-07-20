<?php
namespace Config\Core;

define('ACCESS_TOKEN_LIFETIME', 3600); // 1 hour
define('REFRESH_TOKEN_LIFETIME', 2592000); // 30 days
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'Masuk@1224');
define('JWT_SECRET_READONLY', $_ENV['JWT_SECRET_READONLY'] ?? 'MasukReadonly@1224');

class TokenGenerator {
    public static function generateAccessToken(string $userId) {
        $payload = [
            'user_id' => $userId,
            'type' => 'access',
            'exp' => time() + ACCESS_TOKEN_LIFETIME
        ];
        
        return self::encodeToken($payload, JWT_SECRET);
    }

    public static function generateRefreshToken(string $userId) {
        $payload = [
            'user_id' => $userId,
            'type' => 'refresh',
            'exp' => time() + REFRESH_TOKEN_LIFETIME
        ];
        
        return self::encodeToken($payload, JWT_SECRET);
    }

    public static function generateReadonlyToken(string $userId) {
        $payload = [
            'user_id' => $userId,
            'type' => 'readonly',
            'exp' => time() + ACCESS_TOKEN_LIFETIME
        ];

        return self::encodeToken($payload, JWT_SECRET_READONLY);
    }

    private static function encodeToken($payload, $secret) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = base64_encode($header);
        
        $payload = json_encode($payload);
        $payload = base64_encode($payload);
        
        $signature = hash_hmac('sha256', "$header.$payload", $secret, true);
        $signature = base64_encode($signature);
        
        return "$header.$payload.$signature";
    }

    public static function verifyToken($token): bool|array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signature = $parts[2];

        $validSignature = base64_encode(
            hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", JWT_SECRET, true)
        );

        if ($signature !== $validSignature) {
            return false;
        }

        $payload = json_decode($payload, true);
        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    public static function verifyReadonlyToken($token): bool|array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signature = $parts[2];

        $validSignature = base64_encode(
            hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", JWT_SECRET_READONLY, true)
        );

        if ($signature !== $validSignature) {
            return false;
        }

        $payload = json_decode($payload, true);
        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    public static function getJwtSecret() {
        return JWT_SECRET;
    } 

    public static function getReadonlyJwtSecret() {
        return JWT_SECRET_READONLY;
    }
}