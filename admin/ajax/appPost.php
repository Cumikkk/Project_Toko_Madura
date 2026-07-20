<?php
require_once __DIR__ . "/../../config/setting.php";
require_once CONFIG_ROOT . "/vendor/autoload.php";
use App\Models\Admin;
use Allmedia\Shared\AdminPermission\Core\AdminPermissionCore;
use App\Factory\AdminPermissionFactory;
use Config\Core\Database;

try {
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

    if(empty($_SERVER['HTTP_REFERER'])) {
        JsonResponse([
            'code'      => 403,
            'success'   => false,
            'message'   => "Invalid Request",
            'data'      => []
        ]);
    }

    /** Authentication */
    $user = Admin::authentication();
    if(empty($user)) {
        JsonResponse([
            'code'      => 403,
            'success'   => false,
            'message'   => "Invalid User",
            'data'      => []
        ]);
    }

    $adminPermissionCore = AdminPermissionFactory::adminPermissionCore();
    $authorizedPermission = $adminPermissionCore->getAuthrorizedPermissions($user['ID_ADM']);
    $url = str_replace("/postdata", "", $requestUri);
    require_once $fileUrl;

} catch (Exception $e) {
    if(ini_get("display_errors") == "1") {
        throw $e;
    }

    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Internal Server Error",
        'data'      => []
    ]);
}