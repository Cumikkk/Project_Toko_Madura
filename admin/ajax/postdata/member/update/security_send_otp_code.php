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

/** send OTP request */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

$otpCode = random_int(1000, 9999);
$otpExpired = date("Y-m-d H:i:s", strtotime("+5 minute"));
$setOtp = UserOtpFactory::setOtp($otpCode, $otpExpired, $userData['MBR_ID']);
if(!$setOtp) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal",
        'data' => []
    ]);
}

$emailData = [
    'subject' => "OTP verification",
    'title' => "OTP Verification",
    'name' => $userData['MBR_NAME'],
    'expired' => "5 Menit",
    'otp' => $otpCode,
];

$emailSender = EmailSender::init(['email' => $userData['MBR_EMAIL'], 'name' => $userData['MBR_NAME']]);
$emailSender->useFile('default-otp', $emailData);
$send = $emailSender->send();
if(!$send) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal mengirim OTP",
        'data' => []
    ]);
}

$db->commit();
Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => '/member/update/security_send_otp_code',
    'action' => "Send OTP Request",
    'message' => "Send OTP code to user with email {$userData['MBR_EMAIL']}",
    'data' => [
        'email' => $userData['MBR_EMAIL'],
    ]
]);

JsonResponse([
    'success' => true,
    'message' => "Successfully send OTP code",
    'data' => []
]);