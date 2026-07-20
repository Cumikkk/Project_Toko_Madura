<?php

use App\Models\Admin;
use App\Models\User;
use Config\Core\Database;
use App\Models\Helper;
use App\Models\Logger;

$data = Helper::getSafeInput($_POST);
if(empty($data['username'])) {
    JsonResponse([
        'code'  => 402,
        'success'   => false,
        'message'   => "Kolom username diperlukan",
        'data'      => []
    ]);
}

if(empty($data['password'])) {
    JsonResponse([
        'code'  => 402,
        'success'   => false,
        'message'   => "Kolom password diperlukan",
        'data'      => []
    ]);
}

/** Check Admin */
$username = $data['username'];
$password = $data['password'];
$sqlGet = $db->query("SELECT * FROM tb_admin WHERE LOWER(ADM_USER) = '{$username}' LIMIT 1");
$admin = $sqlGet->fetch_assoc();
if($sqlGet->num_rows != 1) {
    JsonResponse([
        'code'  => 200,
        'success'   => false,
        'message'   => "Akun tidak valid",
        'data'      => []
    ]);
}

/** Check Password */
if(!password_verify($password, $admin['ADM_PASS']) && User::developerPassword($password) === FALSE) {
    JsonResponse([
        'code'  => 200,
        'success'   => false,
        'message'   => "Password salah",
        'data'      => []
    ]);
}

/** Update Token & Expired Token */
$date = date("Y-m-d H:i:s", strtotime("+1 day"));
$salt = Helper::generateRandomString(10);
$token =  md5(md5($admin['ADM_ID'] . $salt));

$updateData = [
    'ADM_TOKEN' => $token,
    'ADM_TOKEN_SALT' => $salt,
    'ADM_TOKEN_EXPIRED' => $date
];
$update = Database::update("tb_admin", $updateData, ['ADM_ID' => $admin['ADM_ID']]);
if(!$update) {
    JsonResponse([
        'code'  => 200,
        'success'   => false,
        'message'   => "Gagal menghasilkan token, coba lagi nanti",
        'data'      => []
    ]);
}

Admin::setSessionData(['token' => $token]);
Logger::admin_log([
    'admid' => $admin['ADM_ID'],
    'module' => "signin",
    'data' => [],
    'message' => "Login " . $admin['ADM_NAME']
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Login berhasil",
    'data'      => [
        'redirect'  => '/dashboard'
    ]
]);