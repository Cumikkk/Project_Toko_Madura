<?php
require_once __DIR__ . "/../../config/setting.php";
use Allmedia\Shared\AdminPermission\Core\UrlParser;
use Ozdemir\Datatables\Datatables;
use Ozdemir\Datatables\DB\MySQL;
use App\Models\Admin;
use App\Factory\AdminPermissionFactory;
use Config\Core\Database;

try {
    global $_SESSION, $_COOKIE;
    global $db;
    $aws_folder = "https://allmediaindo-2.s3.ap-southeast-1.amazonaws.com/gfsprime/";
    $dt = new Datatables( new MySQL([ 
        'host'     => Database::$host,
        'port'     => Database::$port,
        'username' => Database::$username,
        'password' => Database::$password,
        'database' => Database::$database
    ]));
    

    /** validate token */
    $user = Admin::authentication();
    if(empty($user)) {
        JsonResponse([
            'code' => 403,
            'success' => false,
            'message' => "Invalid User",
            'data' => []
        ]);
    }

    /** Admin Permission */
    $adminPermissionCore = AdminPermissionFactory::adminPermissionCore();
    $authorizedPermission = $adminPermissionCore->getAuthrorizedPermissions($user['ID_ADM']);
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $url = str_replace("/ajax/datatable", "", $requestUri);
    $permission = $adminPermissionCore->hasPermission($authorizedPermission, $url);
    if(empty($_SERVER['HTTP_REFERER'])) {
        JsonResponse([
            'code' => 400,
            'success' => false,
            'message' => "Invalid Request",
            'data' => []
        ]);
    }

    function checkPermission(string $permission, string $onTrue, string $onFalse = "") {
        global $authorizedPermission, $adminPermissionCore;
        return ($adminPermissionCore->hasPermission($authorizedPermission, $permission))? $onTrue : $onFalse;
    }


    /** Validate filename */
    if(!$permission) {
        JsonResponse([
            'code' => 403,
            'success' => false,
            'message' => "Invalid Permission",
            'data' => []
        ]);
    }

    $fileUrl = str_replace("/view", "", $permission['fileurl']);
    $fileUrl = UrlParser::urlToPath(explode("/", $fileUrl), "view");
    if(!file_exists(__DIR__ . "/tabledata/{$fileUrl}.php")) {
        JsonResponse([
            'code' => 404,
            'success' => false,
            'message' => "Invalid Url",
            'data' => []
        ]);
    }

    require_once __DIR__ . "/tabledata/{$fileUrl}.php";

} catch (Exception $e) {
    throw $e;
}