<?php
require_once __DIR__ . "/../../config/setting.php";
require_once CONFIG_ROOT . "/vendor/autoload.php";
use Config\Core\Database;

try {
    $db = Database::connect();

    $parseUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = str_replace(['\*', '/ajax', '/auth'], ['', '', '/authentication'], $parseUrl);
    $fileUrl = __DIR__ . $requestUri . ".php";

    if(!file_exists($fileUrl)) {
        JsonResponse([
            'code'      => 404,
            'success'   => false,
            'message'   => "Page NotFound",
            'data'      => []
        ]);
    }

    if(empty($_SERVER['HTTP_REFERER'])) {
        JsonResponse([
            'code'      => 403,
            'success'   => false,
            'message'   => "Invalid Request",
            'data'      => []
        ]);
    }

    require_once $fileUrl;

} catch (Exception $e) {
    if(ini_get("display_errors") == "1") {
        throw $e;
    }

    JsonResponse([
        'code'      => 500,
        'success'   => false,
        'message'   => "Internal Server Error",
        'data'      => []
    ]);
}