<?php

use App\Models\Logger;
use Config\Core\Database;
use Config\Core\EmailSender;

// $sqlCheck = $db->query("SELECT ID_MBANK FROM tb_member_bank WHERE MBANK_MBR = ".$user['MBR_NAME']." AND MBANK_STS = 0 AND MBANK_OTP = '".$otpcode."' LIMIT 1");
// if($sqlCheck->num_rows == 0) {
//     JsonResponse([
//         'success' => false,
//         'message' => "Invalid ID",
//         'data' => []
//     ]);
// }

$otpCode = random_int(1000, 9999);
$otpExpired = date("Y-m-d H:i:s", strtotime("+5 minute"));
$update = Database::update("tb_member_bank", 
[
    'MBANK_OTP' => $otpCode,
    'MBANK_OTP_EXPIRED' => $otpExpired
], [
    'MBANK_MBR' => $user['MBR_ID'],
    'MBANK_STS' => 0
]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui data",
        'data' => []
    ]);
}

/** Email OTP */
$emailData = [
    'subject' => "OTP Verification",
    'otp'  => $otpCode,
];

$emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
$emailSender->useFile("otp", $emailData);
$send = $emailSender->send();

/** Log */
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "resend-otp-bank",
    'data' => array_merge($_POST, $emailData),
    'message' => "resend OTP code " . $user['MBR_EMAIL']
]);

JsonResponse([
    'success' => true,
    'message' => "Check OTP di email anda",
    'data' => []
]);