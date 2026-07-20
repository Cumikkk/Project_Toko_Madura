<?php

use App\Factory\CekatAiFactory;
use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;
use Config\Core\EmailSender;


$uniqueCode = Helper::form_input($_POST['code'] ?? "");
$type = Helper::form_input($_POST['type'] ?? "whatsapp");
$sqlGet = $db->query("SELECT * FROM tb_member WHERE MD5(MD5(CONCAT(MBR_ID, ID_MBR))) = '$uniqueCode' AND MBR_STS = 0 LIMIT 1");
if($sqlGet->num_rows != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

/** user data */
$user = $sqlGet->fetch_assoc(); 

/** check request attempt left */
if(!$user['MBR_OTP_ATTEMPT_LEFT'] || $user['MBR_OTP_ATTEMPT_LEFT'] <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Anda sudah mencapai batas maksimal pengiriman OTP. Mohon menghubungi Customer Service untuk bantuan lebih lanjut.",
        'data' => []
    ]);
}

/** check otp */
if(!empty($user['MBR_OTP_EXPIRED']) && strtotime($user['MBR_OTP_EXPIRED']) >= strtotime("now")) {
    $deliveryDate = new Datetime($user['MBR_OTP_EXPIRED']);
    $diff = (new DateTime())->diff($deliveryDate);

    if($diff->i != 0) {
        JsonResponse([
            'success' => false,
            'message' => "Anda harus menunggu {$diff->i} menit {$diff->s} detik sebelum mengirim ulang",
            'data' => []
        ]);
    }

    if($diff->s != 0) {
        JsonResponse([
            'success' => false,
            'message' => "Anda harus menunggu {$diff->s} detik sebelum mengirim ulang",
            'data' => []
        ]);
    }
}

/** validasi type pengiriman otp */
if(!in_array($type, ['whatsapp', 'email'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

/** update OTP */
$otpCode = random_int(1000, 9999);
$otpMinute = 5;
$otpExpired = date("Y-m-d H:i:s", strtotime("+{$otpMinute} minute"));
$requestAttemptLeft = max(0, $user['MBR_OTP_ATTEMPT_LEFT'] - 1);
$update = Database::update("tb_member", ['MBR_OTP' => $otpCode, 'MBR_OTP_EXPIRED' => $otpExpired, 'MBR_OTP_ATTEMPT_LEFT' => $requestAttemptLeft], ['MBR_ID' => $user['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to send OTP",
        'data' => []
    ]);
}

$isSuccess = false;
switch($type) {
    case "email": 
        /** Email OTP */
        $emailData = [
            'subject' => "OTP Verification",
            'otp'  => $otpCode,
            'otpMinute' => $otpMinute
        ];
        
        $emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
        $emailSender->useFile("otp", $emailData);
        $isSuccess = $emailSender->send();
        break;

    case "whatsapp":
        $cekatOtpService = CekatAiFactory::createOtpService();
        $response = $cekatOtpService->sendOtp($otpCode, $user['MBR_NAME'], $user['MBR_PHONE'], $user['MBR_PHONE_CODE']);
        $isSuccess = $response->success;
        break;

    default: 
        $isSuccess = false;
        break;
}       

if(!$isSuccess) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to send OTP",
        'data' => []
    ]);
}

/** Log */
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "resend-otp",
    'data' => array_merge($_POST, ($emailData ?? [])),
    'message' => "Resend OTP code " . $user['MBR_EMAIL']
]);

JsonResponse([
    'success' => true,
    'message' => "Kode OTP berhasil dikirimkan",
    'data' => [
        'expiredSecond' => $otpMinute * 60
    ]
]);