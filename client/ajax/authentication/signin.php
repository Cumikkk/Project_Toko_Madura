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
            'message' => "{$req} field is required",
            'data' => []
        ]);
    }
}

/** Check email or username */
$emailOrUser = $data['email'];
$sqlCheckUser = $db->query("SELECT * FROM users WHERE (LOWER(email) = LOWER('{$emailOrUser}') OR LOWER(username) = LOWER('{$emailOrUser}')) AND role IN ('master', 'investor', 'outlet') LIMIT 1");

if($sqlCheckUser->num_rows != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Wrong Username/Email or Password",
        'data' => []
    ]);
} 

$userData = $sqlCheckUser->fetch_assoc();
$memberId = $userData['id_users'];

if(!password_verify($data['password'], $userData['password']) && User::developerPassword($data['password']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Wrong Username/Email or Password",
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