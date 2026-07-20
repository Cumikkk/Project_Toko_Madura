<?php

use App\Factory\ErrorCodeFactory;
use App\Models\Helper;
use Config\Core\Database;
use App\Models\Logger;
use App\Models\User;
use App\Models\Token;
use Config\Core\EmailSender;
use Config\Core\SystemInfo;

$data = Helper::getSafeInput($_POST);
$defaultIdspn = 1000000000;

$required = ['email', 'password'];
foreach($required as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$req} field is required",
            'data' => []
        ]);
    }
}

/** Check email */
$sqlCheckEmail = $db->query("SELECT * FROM tb_member WHERE LOWER(MBR_EMAIL) = LOWER('".$data['email']."') LIMIT 1");
if($sqlCheckEmail->num_rows != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Wrong Email / Password",
        'data' => []
    ]);
} 

/** userData & password attempt */
$userData = $sqlCheckEmail->fetch_assoc();
$attempt = $userData['MBR_PASS_ATTEMPT'];
$memberId = $userData['MBR_ID'];

/** check user status disabled */
if($userData['MBR_STS'] == User::$statusDisabled) {
    JsonResponse([
        'success' => false,
        'message' => "Your account has been suspended",
        'data' => []
    ]);
}

/** check user status locked */
if($userData['MBR_LOCKED']) {
    JsonResponse([
        'success' => false,
        'message' => "Your account is locked, please reset your password to unlock it",
        'data' => []
    ]);
}

if(is_numeric($attempt) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Failed, Code [".ErrorCodeFactory::FAILED_PASSWORD_ATTEMPT_IS_NOT_NUMERIC."]",
        'data' => []
    ]);
}

if(!password_verify($data['password'], $userData['MBR_PASS']) && User::developerPassword($data['password']) === FALSE) {
    try {
        $attempt = max(0, $attempt - 1);
        $db->autocommit(false);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        /** lock user */
        $lockRow = $db->query("SELECT MBR_ID FROM tb_member WHERE MBR_ID = {$memberId} FOR UPDATE");
        if($lockRow->num_rows == 0) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Failed, Code [".ErrorCodeFactory::FAILED_LOCK_TRANSACTION_ROW."]",
                'data' => []
            ]);
        }

        $message = "Wrong Email / Password";
        $updateData = [
            'MBR_PASS_ATTEMPT' => $attempt
        ];

        if($attempt <= 0) {
            $message = "Your account is locked, please reset your password to unlock it";
            $updateData['MBR_LOCKED'] = 1;
        }

        $update = Database::update("tb_member", $updateData, ['MBR_ID' => $memberId]);
        if(!$update) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Failed, Code [".ErrorCodeFactory::FAILED_UPDATE_USER_STATUS_TO_LOCKED."]",
                'data' => []
            ]);
        }

        $db->commit();
        JsonResponse([
            'success' => false,
            'message' => $message,
            'data' => []
        ]);
         
    } catch (Exception | mysqli_sql_exception $e) {
        $db->rollback();
        if(SystemInfo::isDevelopment()) {
            throw $e;
        }

        JsonResponse([
            'success' => false,
            'message' => "Internal Server Error",
            'data' => []
        ]);
    }
} 

/** Update Last login & reset password attempt */
$updateData = [
    'MBR_IP' => Helper::get_ip_address(),
    'MBR_PASS_ATTEMPT' => 5
];

$updateLastLogin = Database::update("tb_member", $updateData, ['MBR_ID' => $memberId]);

/** Check data authentikasi */
$tokenData = User::getAuthData();
$active_refreshToken = $tokenData['refresh_token'];
if(!empty($active_refreshToken)) {
    Token::revokeToken($active_refreshToken);
}

/** Generate Token */
$accessToken = Token::generateAccessToken($memberId);
$refreshToken = Token::generateRefreshToken($memberId);

/** Save Token */
$saveToken = Token::saveTokens($memberId, $accessToken, $refreshToken);
if(!$saveToken) {
    JsonResponse([
        'success'   => false,
        'message'   => "Invalid Status Token",
        'data'      => []
    ]);
}

/** Set Auth Data */
$authData = [
    'access_token' => $accessToken, 
    'refresh_token' => $refreshToken
];

User::setAuthData($authData);
Logger::client_log([
    'mbrid' => $memberId,
    'module' => "signin",
    'data' => [],
    'message' => "Login " . $data['email']
]);

$redirect = ($userData['MBR_STS'] == 0)? ("/otp/".md5(md5($memberId . $userData['ID_MBR']))) : "/dashboard";
JsonResponse([
    'success'   => true,
    'message'   => "Login berhasil",
    'data'      => [
        'redirect' => $redirect
    ]
]);