<?php
require_once __DIR__ . "/../../config/setting.php";
require_once CONFIG_ROOT . "/vendor/autoload.php";

use Config\Core\Database;
use App\Models\User;

try {
    header('Content-Type: application/json');
    $db = Database::connect();

    $rawParseUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $ajaxPos = strpos($rawParseUrl, '/ajax');
    $parseUrl = ($ajaxPos !== false) ? substr($rawParseUrl, $ajaxPos) : $rawParseUrl;
    $requestUri = str_replace(['\*', '/ajax', '/get'], ['', '', '/getdata'], $parseUrl);
    $fileUrl = __DIR__ . $requestUri . ".php";

    if (!file_exists($fileUrl)) {
        JsonResponse([
            'code'      => 404,
            'success'   => false,
            'message'   => "Endpoint GET Tidak Ditemukan",
            'data'      => []
        ]);
    }

    /** Authentication */
    $user = User::user();
    if (!$user) {
        JsonResponse([
            'code'      => 403,
            'success'   => false,
            'message'   => "Sesi Pengguna Tidak Valid",
            'data'      => []
        ]);
    }

    require_once $fileUrl;

} catch (Exception $e) {
    if (ini_get("display_errors") == "1") {
        throw $e;
    }

    JsonResponse([
        'code'      => 500,
        'success'   => false,
        'message'   => "Terjadi kesalahan server",
        'data'      => []
    ]);
}
