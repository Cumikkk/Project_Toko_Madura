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
        
        if (empty($_ENV[$input])) {
            if ($input === 'CLIENT_URL' || $input === 'ADMIN_URL') {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $currentFolder = ($input === 'CLIENT_URL') ? 'client' : 'admin';
                
                if (strpos(strtolower($host), $currentFolder) !== false && strpos($scriptName, "/$currentFolder") === false) {
                    return rtrim($protocol . $host, '/');
                }
                
                if (strpos($scriptName, "/$currentFolder") !== false) {
                    $basePath = substr($scriptName, 0, strpos($scriptName, "/$currentFolder") + strlen("/$currentFolder"));
                    return rtrim($protocol . $host . $basePath, '/');
                } else {
                    $parts = explode('/', trim($scriptName, '/'));
                    $projectPath = '';
                    if (count($parts) > 1 && $parts[0] !== 'client' && $parts[0] !== 'admin') {
                        $projectPath = '/' . $parts[0];
                    }
                    return $protocol . $host . $projectPath . '/' . $currentFolder;
                }
            }
            return "";
        }

        return $_ENV[ $input ];
    }
}