<?php

use App\Factory\UserOtpFactory;
use App\Models\Firebase;
use Config\Core\EmailSender;

/** check delay */
$isDelay = UserOtpFactory::isDelay();
if($isDelay !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isDelay ?? "Gagal",
        'data' => []
    ]);
}

$otpCode = random_int(1000, 9999);
$otpExpired = date("Y-m-d H:i:s", strtotime("+5 minute"));
$setOtp = UserOtpFactory::setOtp($otpCode, $otpExpired);
if(!$setOtp) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal",
        'data' => []
    ]);
}

$emailData = [
    'subject' => "Withdrawal OTP",
    'title' => "Withdrawal OTP",
    'name' => $user['MBR_NAME'],
    'expired' => "5 Menit",
    'otp' => $otpCode,
];

$emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
$emailSender->useFile('default-otp', $emailData);
$send = $emailSender->send();

if(!$send) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal mengirim OTP",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Success",
    'data' => []
]);