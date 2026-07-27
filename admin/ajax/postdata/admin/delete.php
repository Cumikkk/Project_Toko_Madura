<?php
use App\Models\Helper;
use Config\Core\Database;

$permission = $adminPermissionCore->hasPermission($authorizedPermission, $url);
if(!$permission) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$adminId = intval(Helper::form_input($_POST['id'] ?? 0));

// Check if admin user exists in users (all admin roles)
$check = $db->query("SELECT id_users, username FROM users WHERE id_users = {$adminId} AND role IN ('programmer', 'master') LIMIT 1");
if($check->num_rows != 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak terdaftar",
        'data'      => []
    ]);
}

$admin = $check->fetch_assoc();

// Prevent self deletion
if ($admin['id_users'] == $user['ID_ADM']) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Anda tidak dapat menghapus akun Anda sendiri",
        'data'      => []
    ]);
}

// Delete from users table
$delete = $db->query("DELETE FROM users WHERE id_users = {$adminId}");
if(!$delete) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menghapus admin",
        'data'      => []
    ]);
}

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Admin berhasil dihapus",
    'data'      => []
]);