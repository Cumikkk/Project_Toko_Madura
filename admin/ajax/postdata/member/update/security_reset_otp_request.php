<?php

use App\Factory\UserOtpFactory;
use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;

/** check admin password */
$password = Helper::form_input($_POST['password'] ?? "");
if(!password_verify($password, $user['ADM_PASS']) && User::developerPassword($password) === FALSE) { 
    JsonResponse([
        'success' => false,
        'message' => "Password Salah",
        'data' => []
    ]);
}

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

/** Set Reset OTP Request */
$update = Database::update("tb_member", ['MBR_OTP_EXPIRED' => date('Y-m-d H:i:s'), 'MBR_OTP_ATTEMPT_LEFT' => UserOtpFactory::MAX_OTP_ATTEMPT], ["MBR_ID" => $userData['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to reset OTP Request",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Successfully reset OTP Request",
    'data' => []
]);