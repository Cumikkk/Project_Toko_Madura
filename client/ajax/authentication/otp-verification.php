<?php

use App\Factory\MetatraderFactory;
use App\Factory\UserOtpFactory;
use App\Models\Account;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\ProfilePerusahaan;
use App\Models\Token;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;
use Config\Core\SystemInfo;

function createDemo(): bool {
    global $user;
    /** create demo account */
    $demoAccount = Account::getDemoAccount(md5(md5($user['MBR_ID'])));
    if(empty($demoAccount)) {
        $createDemo = MetatraderFactory::createDemo($user['MBR_NAME'], $user['MBR_EMAIL']);
        if(!$createDemo['success']) {
            Logger::client_log([
                'mbrid' => $user['MBR_ID'],
                'module' => "otp-verification",
                'message' => "Failed to Create Demo Account",
                'data' => $createDemo
            ]);
            return false;
        }

        /** Insert Demo */
        $demoData = $createDemo['data'];
        $insertDemo = Database::insert("tb_racc", [
            'ACC_MBR' => $user['MBR_ID'],
            'ACC_DERE' => 2,
            'ACC_TYPE' => $demoData['type'],
            'ACC_LOGIN' => $demoData['login'],
            'ACC_PASS' => $demoData['password'],
            'ACC_INVESTOR' => $demoData['investor'],
            'ACC_PASSPHONE' => $demoData['passphone'],
            'ACC_INITIALMARGIN' => MetatraderFactory::$initMarginDemo,
            'ACC_FULLNAME' => $user['MBR_NAME'],
            'ACC_DATETIME' => date("Y-m-d H:i:s"),
        ]);

        if(!$insertDemo) {
            Logger::client_log([
                'mbrid' => $user['MBR_ID'],
                'module' => "otp-verification",
                'message' => "Failed assign demo account to user {$user['MBR_EMAIL']} ",
                'data' => $demoData
            ]);

            return false;
        }

        /** Send Notification Email */
        $emailData = [
            "subject" => "Demo Account Information - ". ProfilePerusahaan::get()['COMPANY_NAME'] ." ".date('Y-m-d H:i:s'),
            "name" => $user["MBR_NAME"],
            "login" => $demoData['login'],
            "metaPassword"  => $demoData['password'],
            "metaInvestor"  => $demoData['investor'],
            "metaPassPhone" => $demoData['passphone'],
        ];
        
        $emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
        $emailSender->useFile("create-demo", $emailData);
        $send = $emailSender->send();
        return true;
    }

    return true;
}

function createTemporaryDemo(): bool {
    global $db, $user;
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($db);

    /** Insert tb_racc */
    $maxIdResult = $db->query("SELECT MAX(ID_ACC) as MAX_ID FROM tb_racc")->fetch_assoc();
    $maxId = ($maxIdResult['MAX_ID'] ?? 0) + 1;
    $insertDemo = Database::insert("tb_racc", [
        'ACC_MBR' => $user['MBR_ID'],
        'ACC_DERE' => 2,
        'ACC_TYPE' => 'temporary-demo',
        'ACC_LOGIN' => "999{$maxId}",
        'ACC_PASS' => uniqid(),
        'ACC_INVESTOR' => uniqid(),
        'ACC_PASSPHONE' => uniqid(),
        'ACC_INITIALMARGIN' => MetatraderFactory::$initMarginDemo,
        'ACC_FULLNAME' => "Temporary Demo User",
        'ACC_DATETIME' => date("Y-m-d H:i:s"),
    ]);

    if(!$insertDemo) {
        $db->rollback();
        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "otp-verification",
            'message' => "Failed assign temporary demo account to user {$user['MBR_EMAIL']} ",
            'data' => []
        ]);

        return false;
    }

    /** insert mt4_users */
    $metaDb = SystemInfo::app('DB_METADEMO');
    $insertMT4Users = Database::insert("meta_firststatedemo.MT4_USERS", [
        'login' => "999{$maxId}",
        'group' => 'temporary-demo',
        'name' => "Temporary Demo User",
        'email' => $user['MBR_EMAIL'],
        'leverage' => 100,
    ]);

    if(!$insertMT4Users) {
        $db->rollback();
        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "otp-verification",
            'message' => "Failed insert temporary demo account to mt4_users for user {$user['MBR_EMAIL']} ",
            'data' => []
        ]);

        return false;
    }

    $db->commit();
    return true;
}

$data = Helper::getSafeInput($_POST);
$required = ['otp', 'code'];
foreach($required as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Message",
            'data' => []
        ]);
    }
}

/** check code */
$uniqueCode = $db->real_escape_string($data['code']);
$sqlGet = $db->query("SELECT * FROM tb_member WHERE MD5(MD5(CONCAT(MBR_ID, ID_MBR))) = '$uniqueCode' AND MBR_STS = 0 LIMIT 1");
if($sqlGet->num_rows != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

/** check otp */
$user = $sqlGet->fetch_assoc();
if($user['MBR_OTP'] !== $data['otp']) {
    JsonResponse([
        'success' => false,
        'message' => "Kode Otp Salah",
        'data' => []
    ]);
}

/** check expired */
if(empty($user['MBR_OTP_EXPIRED']) || strtotime($user['MBR_OTP_EXPIRED']) < strtotime("now")) {
    JsonResponse([
        'success' => false,
        'message' => "Kode OTP kadaluarsa",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Update Sts */
$update = Database::update("tb_member", ['MBR_STS' => 2, 'MBR_OTP' => NULL, 'MBR_OTP_EXPIRED' => date("Y-m-d H:i:s"), 'MBR_OTP_ATTEMPT_LEFT' => UserOtpFactory::MAX_OTP_ATTEMPT], ['MBR_ID' => $user['MBR_ID']]);
if(!$update) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Verification failed",
        'data' => []
    ]);
}

/** Generate Token */
$accessToken = Token::generateAccessToken($user['MBR_ID']);
$refreshToken = Token::generateRefreshToken($user['MBR_ID']);

/** Save Token */
$saveToken = Token::saveTokens($user['MBR_ID'], $accessToken, $refreshToken);
if(!$saveToken) {
    $db->rollback();
    JsonResponse([
        'success'   => false,
        'message'   => "Invalid Token",
        'data'      => []
    ]);
}

/** Set Token to Cookie */
User::setAuthData([
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken
]);

$db->commit();

/** Create Demo Account */
if(!createDemo()) {
    $createTemporary = createTemporaryDemo();
}

Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "otp-verification",
    'message' => "OTP Verification",
    'data' => $data
]);

JsonResponse([
    'success'   => true,
    'message'   => "Verifikasi OTP berhasil",
    'data'      => [
        'redirect' => "/verif/step-1"
    ]
]);