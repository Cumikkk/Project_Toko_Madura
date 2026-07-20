<?php

use App\Factory\UserOtpFactory;
use App\Models\Helper;
use App\Models\Logger;
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

/** send otp verification */
$otpCode = random_int(1000, 9999);
$otpExpired = date("Y-m-d H:i:s", strtotime("+5 minute"));
$setOtp = UserOtpFactory::setOtp($otpCode, $otpExpired, $userData['MBR_ID']);
if(!$setOtp) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal mengirim OTP",
        'data' => []
    ]);
}

$emailData = [
    'subject' => "OTP Verification",
    'otp'  => $otpCode,
];

$emailSender = EmailSender::init(['email' => $userData['MBR_EMAIL'], 'name' => $userData['MBR_NAME']]);
$emailSender->useFile("otp", $emailData);
$send = $emailSender->send();

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "send_email_otp_verification",
    'message' => "Send Email OTP Verification to " . $userData['MBR_EMAIL'],
    'data' => [
        'email' => $userData['MBR_EMAIL'],
        'datetime' => date("Y-m-d H:i:s"),
        'status' => $send ?? false
    ]
]);

JsonResponse([
    'success' => true,
    'message' => "Berhasil mengirim OTP",
    'data' => []
]);