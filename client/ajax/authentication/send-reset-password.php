<?php

use Config\Core\Database;
use App\Models\Helper;
use App\Models\User;
use Config\Core\EmailSender;

$email = Helper::form_input($_POST['email'] ?? "");
if(empty($email)) {
    JsonResponse([
        'success' => false,
        'message' => "Email field is required",
        'data' => []
    ]);
}

/** Check Email */
$sqlGet = $db->query("SELECT MBR_ID, MBR_EMAIL, MBR_NAME, MBR_RESET_CODE, MBR_RESET_EXPIRED FROM tb_member WHERE LOWER(MBR_EMAIL) = LOWER('{$email}') LIMIT 1");
if($sqlGet->num_rows != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Email not registered",
        'data' => []
    ]);
}

$userData = $sqlGet->fetch_assoc();
$expired = $userData['MBR_RESET_EXPIRED'] ?? null;
if(!empty($expired) && strtotime($expired) > time()) {
    JsonResponse([
        'success' => false,
        'message' => "Please wait before requesting a new reset code. Try again on " . date("d M Y H:i:s", strtotime($expired)),
        'data' => []
    ]);
}

/** Send Email */
$initData = [
    'email' => $userData['MBR_EMAIL'], 
    'name' => $userData['MBR_NAME']
];

$code = md5(md5(uniqid() . $userData['MBR_ID']));
$emailData = [
    'subject' => "Reset Password",
    'code' => $code
];

$emailSender = EmailSender::init($initData);
$emailSender->useFile("reset-password", $emailData);
$send = $emailSender->send();
if(!$send) {
    JsonResponse([
        'success' => false,
        'message' => "Failed",
        'data' => []
    ]);
}

/** Set Reset Password */
if(!User::setResetPasswordCode($userData['MBR_ID'], $code)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Status",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Reset Password link has been sent to your email",
    'data' => []
]);