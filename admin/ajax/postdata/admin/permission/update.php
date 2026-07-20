<?php
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Logger;

$permission = $adminPermissionCore->hasPermission($authorizedPermission, $url);
if(!$permission && $user['ADM_LEVEL'] != 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['permission_id', 'admin_id', 'status'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}

/** Check Admin Id */
$admin = Admin::findById($data['admin_id']);
if(!$admin) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak valid",
        'data'      => []
    ]);
}

if($admin['ADM_LEVEL'] < $user['ADM_LEVEL']) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Level permission denied",
        'data'      => []
    ]);
}

/** check permission id */
if(is_numeric($data['permission_id']) === FALSE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Permission ID harus berupa numeric",
        'data'      => []
    ]);
}

$permission_id = $data['permission_id'];
$sqlGetPermission = $db->query("SELECT * FROM admin_permissions WHERE id = {$permission_id} LIMIT 1");
if($sqlGetPermission->num_rows != 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Permission ID tidak ditemukan",
        'data'      => []
    ]);
}

if(!in_array($data['status'], ['false', 'true'])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid Status",
        'data'      => []
    ]);
}

/** Check Admin Role */
$status = ($data['status'] == "true")? 1 : 0;
$sqlInsert = $db->query("
    INSERT INTO admin_authorize SET
    admin_id = ".$admin['ID_ADM'].",
    permission_id = {$permission_id},
    status = {$status}
    ON DUPLICATE KEY UPDATE status = {$status}
");

if(!$sqlInsert) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal memperbarui status",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "admin-permission",
    'message' => "Update admin permission",
    'data'  => ['permission_id' => $permission_id, 'status' => $status]
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Permission berhasil diperbarui",
    'data'      => []
]);