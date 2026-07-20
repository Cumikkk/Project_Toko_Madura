<?php
use App\Factory\PermissionGroupFactory;
use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$idGroup = Helper::form_input($_POST['id'] ?? "");
$group = PermissionGroupFactory::init()->getById($idGroup);
if(!$group) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid ID",
        'data'      => []
    ]);
}

/** Update */
$delete = Database::delete("admin_module_group", ['id' => $group['id']]);
if(!$delete) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menghapus grup",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "group",
    'message' => "Menghapus module permission group ".$group['group'],
    'ip' => Helper::get_ip_address(),
    'data' => $group
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Berhasil menghapus group",
    'data'      => []
]);