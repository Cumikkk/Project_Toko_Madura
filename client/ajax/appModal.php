<?php
require_once __DIR__ . "/../../config/setting.php";
require_once CONFIG_ROOT . "/vendor/autoload.php";

use Config\Core\Database;
use App\Models\User;
use App\Models\ApiMetatrader;
use App\Models\Account;


try {
    $db = Database::connect();
    $parseUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = str_replace(['\*', '/ajax', '/modal'], ['', '', '/modals'], $parseUrl);
    $fileUrl = __DIR__ . $requestUri . ".php";

    if(!file_exists($fileUrl)) {
        exit("<div>Page not found</div>");
    }

    /** Authentication */
    $user = User::user();
    if(!$user) {
        exit("<div>Invalid Session</div>");
    }
    
    $userid = md5(md5($user['MBR_ID']));
    $avatar = User::avatar($user['MBR_AVATAR']);
    require_once $fileUrl;

} catch (Exception $e) {
    if(ini_get("display_errors") == "1") {
        throw $e;
    }

    exit("<div>Internal Server Error</div>");
}