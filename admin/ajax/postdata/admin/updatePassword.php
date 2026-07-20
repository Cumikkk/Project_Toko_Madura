<?php
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Logger;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/admin/update/*")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['admin_id', 'new-password'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Kolom {$req} diperlukan",
            'data'      => []
        ]);
    }
}

/** check admin id */
$admin = Admin::findById($data['admin_id']);
if(!$admin) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak terdaftar",
        'data'      => []
    ]);
}

/** validasi password */
$check_password = Helper::validation_password($data['new-password']);
if($check_password !== TRUE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => $check_password,
        'data'      => []
    ]);
}

/** Update Password */
$update = Database::update("tb_admin", ['ADM_PASS' => password_hash($data['new-password'], PASSWORD_BCRYPT)], ['ADM_ID' => $admin['ADM_ID']]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal memperbarui password",
        'data'      => []
    ]);
}

unset($data['new-password']);
Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "admin",
    'message' => "Memperbarui password admin " . $admin['ADM_USER'],
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Password berhasil diperbarui",
    'data'      => []
]);