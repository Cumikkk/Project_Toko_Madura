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
foreach(['admin_id', 'fullname', 'username'] as $req) {
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
$fullname = $data['fullname'];
$username = $data['username'];

// Check if admin user exists in users
$check = $db->query("SELECT id_users FROM users WHERE id_users = {$admin_id} AND role = 'master' LIMIT 1");
if($check->num_rows != 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak ditemukan",
        'data'      => []
    ]);
}

if(!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    JsonResponse([
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh huruf dan angka (tanpa spasi)",
        'data'      => []
    ]);
}

// Check username uniqueness
$sql_check_username = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('".$db->real_escape_string($username)."') AND id_users != {$admin_id} LIMIT 1");
if($sql_check_username->num_rows != 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Username sudah terdaftar",
        'data'      => []
    ]);
}

// Update users table
$update = Database::update("users", [
    'nama_lengkap' => $fullname,
    'username'     => $username
], ['id_users' => $admin_id]);

if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal memperbarui admin",
        'data'      => []
    ]);
}

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Update admin successfully",
    'data'      => [
        'redirect' => \Config\Core\SystemInfo::app('ADMIN_URL') . "/admin/view"
    ]
]);