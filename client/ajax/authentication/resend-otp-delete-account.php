<?php

use App\Factory\UserOtpFactory;
use App\Models\Account;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\MemberBank;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;

$email = Helper::form_input($_POST['email'] ?? "");
if(empty($email)) {
    JsonResponse([
        'success' => false,
        'message' => "Kolom email diperlukan",
        'data' => []
    ]);
}

/** check email dan apakah termasuk disable account */
$userData = User::findByEmail($email);
if(!$userData || empty($userData['MBR_STS']) || $userData['MBR_STS'] != -1) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Email",
        'data' => []
    ]);
}

/** check requirement delete account */
if(!User::checkReqDeleteAccount($userData['MBR_ID'])) {
    JsonResponse([
        'success' => false,
        'message' => "Akun anda sudah dalam pengajuan hapus akun",
        'data' => []
    ]);
}

/** Check pengiriman otp terkahir */
$isSendOtpDelay = UserOtpFactory::isDelay($userData['MBR_ID']);
if($isSendOtpDelay !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isSendOtpDelay ?? "Invalid Status",
        'data' => []
    ]);
}

/** update OTP */
$otpCode = random_int(1000, 9999);
$otpMinute = 10;
$otpExpired = date("Y-m-d H:i:s", strtotime("+{$otpMinute} minute"));
$setOtp = UserOtpFactory::setOtp($otpCode, $otpExpired, $userData['MBR_ID']);
if(!$setOtp) {
    JsonResponse([
        'success' => false,
        'message' => "Pengiriman OTP Gagal",
        'data' => []
    ]);
}

/** Email OTP Delete Account */
$emailData = [
    'subject' => "Delete account OTP",
    'otp'  => $otpCode,
];

$emailSender = EmailSender::init(['email' => $userData['MBR_EMAIL'], 'name' => $userData['MBR_NAME']]);
$emailSender->useFile("otp-delete", $emailData);
$send = $emailSender->send();

/** Log */
Logger::client_log([
    'mbrid' => $userData['MBR_ID'],
    'module' => "send-otp-delete",
    'data' => array_merge($_POST, $emailData),
    'message' => "Send OTP delete code " . $userData['MBR_EMAIL']
]);

JsonResponse([
    'success' => true,
    'message' => "OTP Berhasil Dikirim",
    'data' => [
        'expiredSecond' => $otpMinute * 60
    ]
]);