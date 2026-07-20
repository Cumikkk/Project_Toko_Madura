<?php
use App\Models\Helper;
use App\Models\Admin;
use Config\Core\Database;
use App\Models\Logger;

$permission = $adminPermissionCore->hasPermission($authorizedPermission, $url);
if(!$permission) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

/** check admin id */
$adminId = Helper::form_input($_POST['id'] ?? 0);
$admin = Admin::findById($adminId);
if(!$admin) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak terdaftar",
        'data'      => []
    ]);
}

/** update */
$update = Database::update("tb_admin", ['ADM_STS' => 0], ['ID_ADM' => $adminId]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menonaktifkan admin",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "admin",
    'message' => "Menonaktifkan admin " . $admin['ADM_USER'],
    'data'  => $admin
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Admin berhasil di non-aktifkan",
    'data'      => []
]);