<?php
use App\Models\Helper;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/admin/create")) {
    JsonResponse([
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['add-fullname', 'add-username', 'add-password'] as $req) {
    if(empty($data[ $req ])) {
        $req = str_replace("add-", "", $req);
        JsonResponse([
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}

$add_fullname   = $data['add-fullname'];
$add_username   = $data['add-username'];
$add_password   = $data['add-password'];

// Check username in users table
$sql_check_username = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('".$db->real_escape_string($add_username)."') LIMIT 1");
if(!$sql_check_username || $sql_check_username->num_rows != 0) {
    JsonResponse([
        'success'   => false,
        'message'   => "Username sudah digunakan",
        'data'      => []
    ]);
}

if(!preg_match('/^[a-zA-Z0-9]+$/', $add_username)) {
    JsonResponse([
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh huruf dan angka (tanpa spasi)",
        'data'      => []
    ]);
}

// Check password strength
$check_password = Helper::validation_password($add_password);
if($check_password !== TRUE) {
    JsonResponse([
        'success'   => false,
        'message'   => $check_password,
        'data'      => []
    ]);
}

// Insert into users table
$insert = Database::insert("users", [
    'nama_lengkap' => $add_fullname,
    'username'     => $add_username,
    'password'     => password_hash($add_password, PASSWORD_BCRYPT),
    'role'         => 'master'
]);

if(!$insert) {
    JsonResponse([
        'success'   => false,
        'message'   => "Gagal membuat admin baru",
        'data'      => []
    ]);
}

JsonResponse([
    'success'   => true,
    'message'   => "create admin successfully",
    'data'      => [
        'redirect' => \Config\Core\SystemInfo::app('ADMIN_URL') . "/admin/view"
    ]
]);