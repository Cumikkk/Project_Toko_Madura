<?php

use App\Factory\ErrorCodeFactory;
use Config\Core\Database;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\User;
use Mailgun\Api\MailingList\Member;

$data = Helper::getSafeInput($_POST);
$required = [
    'code' => "Code",
    'password' => "New Password",
    'password_confirm' => "Password Confirmation"
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "$text field is required",
            'data' => []
        ]);
    }
}

/** Validasi Code */
$isValidCode = User::verifyResetCode($data['code']);
if(!$isValidCode) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

/** Validasi password baru */
$isValidPassword = User::validation_password($data['password']);
if($isValidPassword !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isValidPassword,
        'data' => []
    ]);
}

/** Validasi Confirm password */
if(base64_encode($data['password']) != base64_encode($data['password_confirm'])) {
    JsonResponse([
        'success' => false,
        'message' => "Wrong Password Confirmation",
        'data' => []
    ]);
}

$userData = User::findByMemberId($isValidCode);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid User",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** update user status ke active jika akun dalam status locked */
if($userData['MBR_LOCKED']) { 
    $updateUser = Database::update("tb_member", ['MBR_LOCKED' => 0], ['MBR_ID' => $isValidCode]);
    if(!$updateUser) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Failed, Code [".ErrorCodeFactory::FAILED_UPDATE_USER_STATUS_TO_ACTIVE."]",
            'data' => []
        ]);
    }
}

/** Update Password */
$passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
$update = Database::update("tb_member", ['MBR_PASS' => $passwordHash, 'MBR_PASS_ATTEMPT' => 5, 'MBR_RESET_EXPIRED' => date("Y-m-d H:i:s")], ['MBR_ID' => $isValidCode]);
if(!$update) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed update update password",
        'data' => []
    ]);
}

$db->commit();
Logger::client_log([
    'mbrid' => $isValidCode,
    'module' => "reset-password",
    'message' => "Reset Password",
    'data' => [
        'device' => Helper::get_user_agent()
    ]
]);

JsonResponse([
    'success' => true,
    'message' => "Reset password successfull",
    'data' => []
]);