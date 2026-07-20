<?php

use App\Factory\UserOtpFactory;
use App\Models\Helper;
use App\Models\User;
use Config\Core\EmailSender;

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

/** Set Reset Password */
$code = md5(md5(uniqid() . $userData['MBR_ID']));
if(!User::setResetPasswordCode($userData['MBR_ID'], $code)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Status",
        'data' => []
    ]);
}

/** Send Email */
$initData = [
    'email' => $userData['MBR_EMAIL'], 
    'name' => $userData['MBR_NAME']
];

$emailData = [
    'subject' => "Reset Password",
    'code' => $code
];

$emailSender = EmailSender::init($initData);
$emailSender->useFile("reset-password", $emailData);
$send = $emailSender->send();

JsonResponse([
    'success' => true,
    'message' => "Berhasil mengirimkan Email Reset Password",
    'data' => []
]);