<?php
namespace Config\Core;

class SystemInfo {

    public static function isDevelopment(): bool {
        return (ini_get('display_errors') == "1")? true : false; 
    }

    public static function appMode(): string|bool {
        global $_ENV;
        return $_ENV['APP_MODE'] ?? false;
    }

    public static function refreshSession() {
        if(session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 3600, // 1Jam
                'path' => "/",
                'domain' => "",
                'secure' => false,
                'httponly' => true,
                'samesite' => "Lax"
            ]);

            session_start();
        }

        session_regenerate_id();
    }

    public static function app(?string $input) {
        global $_ENV;
        $input ??= "";
        if(!array_key_exists($input, $_ENV)) {
            return "";
        }

        return $_ENV[ $input ];
    }
}