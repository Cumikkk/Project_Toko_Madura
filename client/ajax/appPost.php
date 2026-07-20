<?php
require_once __DIR__ . "/../../config/setting.php";
require_once CONFIG_ROOT . "/vendor/autoload.php";

use Config\Core\Database;
use App\Models\User;
use App\Models\ApiMetatrader;
use App\Models\Account;


try {
    header('Content-Type: application/json');
    $db = Database::connect();

    $parseUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = str_replace(['\*', '/ajax', '/post'], ['', '', '/postdata'], $parseUrl);
    $fileUrl = __DIR__ . $requestUri . ".php";

    if(!file_exists($fileUrl)) {
        JsonResponse([
            'code'      => 404,
            'success'   => false,
            'message'   => "Page NotFound",
            'data'      => []
        ]);
    }

    // if(empty($_SERVER['HTTP_REFERER'])) {
    //     JsonResponse([
    //         'code'      => 403,
    //         'success'   => false,
    //         'message'   => "Invalid Request",
    //         'data'      => []
    //     ]);
    // }

    /** Authentication */
    $user = User::user();
    if(!$user) {
        JsonResponse([
            'code'      => 403,
            'success'   => false,
            'message'   => "Invalid User",
            'data'      => []
        ]);
    }
    
    $userid = md5(md5($user['MBR_ID']));
    $avatar = User::avatar($user['MBR_AVATAR']);
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