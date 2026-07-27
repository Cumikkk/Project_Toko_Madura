<?php
use App\Models\Helper;
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

$admin_id = intval($data['admin_id']);

// Check if admin user exists in users (all admin roles)
$check = $db->query("SELECT id_users FROM users WHERE id_users = {$admin_id} AND role IN ('programmer', 'master') LIMIT 1");
if($check->num_rows != 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak terdaftar",
        'data'      => []
    ]);
}

// Validate password
$check_password = Helper::validation_password($data['new-password']);
if($check_password !== TRUE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => $check_password,
        'data'      => []
    ]);
}

// Update password in users table
$update = Database::update("users", ['password' => password_hash($data['new-password'], PASSWORD_BCRYPT)], ['id_users' => $admin_id]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal memperbarui password",
        'data'      => []
    ]);
}

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Password berhasil diperbarui",
    'data'      => []
]);