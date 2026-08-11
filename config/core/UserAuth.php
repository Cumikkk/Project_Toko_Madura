<?php
namespace Config\Core;

use Exception;

class UserAuth {

    public static function setAuthData(array $data): bool {
        return true;
    }

    public static function getAuthData(): array {
        return [];
    }

    public static function developerPassword(string $password = "") {
        global $_ENV;
        return ($_ENV['APP_DEV_PASS'] ?? '') !== '' && $_ENV['APP_DEV_PASS'] === $password;
    }

    public static function authentication(): bool|string|int {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                return false;
            }
            
            return $userId;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        return true;
    }
}