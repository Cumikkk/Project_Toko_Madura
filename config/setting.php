<?php

use App\Factory\LoggingFactory;
use App\Models\CompanyProfile;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Dotenv\Dotenv;

/** Required Class */
require_once(__DIR__ . "/vendor/autoload.php");
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

SystemInfo::refreshSession();

date_default_timezone_set("Asia/Jakarta");
error_reporting(E_ALL );
ini_set("display_errors", (($_ENV['APP_MODE'] ?? 'development') == "production"? 0 : 1));
define("CONFIG_ROOT", __DIR__);
define("WEB_ROOT", str_replace("config", "client", __DIR__));
define("CRM_ROOT", str_replace("config", "admin", __DIR__));
define("MOBILE_VIEW_ROOT", str_replace("config", "mobile-web-view", __DIR__));

try {
    $db = Database::connect();
} catch (Exception $e) {
    $db = null;
}
CompanyProfile::init();

function JsonResponse(array $data = []) {
    /** ini tidak membaca script dibawahnya */
    http_response_code($data['code'] ?? 200);
    $alert = $data['alert'] ?? [
        'title' => (!empty($data['success']))? "Success" : "Failed",
        'text' => $data['message'] ?? '',
        'icon' => (!empty($data['success']))? "success" : "error"
    ];
    exit(json_encode([
        ...$data,
        'alert' => $alert
    ]));
}

function pathbreadcrumb($level) {
    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
             
    $uri = $_SERVER['REQUEST_URI']; 
    $parts = explode('/', trim($uri, '/')); 
    
    if ($level == 0) {
        return $base;
    }
    
    return $base . '/' . implode('/', array_slice($parts, 0, $level));
}

/** Logging Core */
$loggingCore = LoggingFactory::make();
set_error_handler(function($severity, $message, $file, $line) use ($loggingCore) {
    global $user;
    $loggingCore->error("PHP Error: {$message} in {$file} on line {$line}", [
        'severity' => $severity,
        'file' => $file,
        'line' => $line,
        'user' => $user['MBR_EMAIL'] ?? null
    ]);

    if(SystemInfo::isDevelopment()) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

set_exception_handler(function($exception) use ($loggingCore) {
    global $user;
    $loggingCore->error("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'stack_trace' => $exception->getTraceAsString(),
        'user' => $user['MBR_EMAIL'] ?? null
    ]);
   
    if(SystemInfo::isDevelopment()) {
        throw new ErrorException($exception->getMessage(), 0, E_ERROR, $exception->getFile(), $exception->getLine());
    }
});