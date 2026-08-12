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

$sqlGet = $db->query("SELECT * FROM users WHERE LOWER(username) = LOWER('{$username}') AND role IN ('admin', 'master') LIMIT 1");

if(!$sqlGet || $sqlGet->num_rows != 1) {
    JsonResponse([
        'code'  => 200,
        'success'   => false,
        'message'   => "Akun tidak valid",
        'data'      => []
    ]);
    exit;
}

$admin = $sqlGet->fetch_assoc();

/** Check Password */
if(!password_verify($password, $admin['password']) && User::developerPassword($password) === FALSE) {
    JsonResponse([
        'code'  => 200,
        'success'   => false,
        'message'   => "Password salah",
        'data'      => []
    ]);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = md5(uniqid($admin['id_users'], true));
$_SESSION['user_id'] = $admin['id_users'];

Admin::setSessionData(['token' => $token]);

$redirectUrl = (strtolower($admin['role'] ?? '') === 'master') ? SystemInfo::app('CLIENT_URL') . '/investor' : 'dashboard';

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Login berhasil",
    'data'      => [
        'redirect'  => $redirectUrl
    ]
]);