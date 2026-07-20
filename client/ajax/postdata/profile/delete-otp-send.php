<?php

use App\Factory\UserOtpFactory;
use App\Models\Logger;
use Config\Core\Database;
use Config\Core\EmailSender;

try {
    global $db;
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($db);

    $isDelay = UserOtpFactory::isDelay();
    if($isDelay !== TRUE) {
        JsonResponse([
            'success' => false,
            'message' => is_string($isDelay)? $isDelay : "Terlalu banyak permintaan. Silakan coba beberapa saat lagi.",
            'data' => []
        ]);
    }

    $otpCode = random_int(1000, 9999);
    $otpExpired = date("Y-m-d H:i:s", strtotime("+5 minute"));
    if(!UserOtpFactory::setOtp($otpCode, $otpExpired)) {
        JsonResponse([
            'success' => false,
            'message' => "Permintaan OTP gagal",
            'data' => []
        ]);
    }

    $update = Database::update("tb_member", ['MBR_OTP' => $otpCode, 'MBR_OTP_EXPIRED' => $otpExpired], ['MBR_ID' => $user['MBR_ID']]);
    if(!$update) {
        JsonResponse([
            'success' => false,
            'message' => "Gagal memperbarui data",
            'data' => []
        ]);
    }

    /** Email OTP for member*/
    $emailData = [
        'subject' => "Delete account OTP",
        'otp'  => $otpCode,
    ];

    $emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
    $emailSender->useFile("otp-delete", $emailData);
    $send = $emailSender->send();

    mysqli_commit($db);
} catch (Exception | mysqli_sql_exception $e) {
    mysqli_rollback($db);

    JsonResponse([
        'success' => false,
        'message' => "Exception occured",
        'data' => []
    ]);
}

/** Log */
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "send-otp-delete",
    'data' => array_merge($_POST, $emailData),
    'message' => "Send OTP delete code " . $user['MBR_EMAIL']
]);

JsonResponse([
    'success' => true,
    'message' => "Check OTP di email anda",
    'data' => []
]);