<?php

use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;

/** check user */
$userCode = Helper::form_input($_POST['code'] ?? "");
$userData = User::findByCode($userCode);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Userdata",
        'data' => []
    ]);
}

$newPassword = Helper::form_input($_POST['new-password'] ?? "");
if(empty($newPassword)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Password",
        'data' => []
    ]);
}

/** validasi password */
$isValidPassword = User::validation_password($newPassword);
if($isValidPassword !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isValidPassword ?? "Invalid Password",
        'data' => []
    ]);
}

/** update password */
$passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
$update = Database::update("tb_member", ['MBR_PASS' => $passwordHash], ['MBR_ID'=> $userData['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui password",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil memperbarui password",
    'data' => []
]);