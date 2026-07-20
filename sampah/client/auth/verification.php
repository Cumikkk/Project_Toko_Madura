<?php

use App\Factory\MetatraderFactory;
use App\Models\Helper;
use App\Models\ProfilePerusahaan;
use Config\Core\Database;
use App\Models\Token;
use App\Models\User;
use Config\Core\EmailSender;

$data = Helper::getSafeInput($_GET);
$verificationCode = $data['b'] ?? "";

if(empty($verificationCode)) {
    die("<script>alert('Invalid code'); location.href = '/';</script>");
}

$sqlGet = $db->query("SELECT * FROM tb_member WHERE MD5(MD5(CONCAT(MBR_ID, MBR_OTP))) = '$verificationCode' AND MBR_STS = 0 LIMIT 1");
$userData = $sqlGet->fetch_assoc();
if($sqlGet->num_rows != 1) {
    die("<script>alert('Invalid verification code'); location.href = '/';</script>");
}

/** Check Code expired */
if(strtotime($userData['MBR_OTP_EXPIRED']) < strtotime("now")) {
    die("<script>alert('Verification code expired'); location.href = '/';</script>");
}

/** Update Sts */
$update = Database::update("tb_member", ['MBR_STS' => 2], ['MBR_ID' => $userData['MBR_ID']]);
if(!$update) {
    die("<script>alert('Verification failed'); location.href = '/';</script>");
}

/** Generate Token */
$accessToken = Token::generateAccessToken($userData['MBR_ID']);
$refreshToken = Token::generateRefreshToken($userData['MBR_ID']);

/** Save Token */
$saveToken = Token::saveTokens($userData['MBR_ID'], $accessToken, $refreshToken);
if(!$saveToken) {
    JsonResponse([
        'success'   => false,
        'message'   => "Failed to save token",
        'data'      => []
    ]);
}

/** Set Token to Cookie */
User::setAuthData([
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken
]);

/** create demo account */
$createDemo = MetatraderFactory::createDemo($userData['MBR_NAME'], $userData['MBR_EMAIL']);
if(!$createDemo['success']) {
    $db->rollback();
    JsonResponse([
        'success'   => false,
        'message'   => $createDemo['message'] ?? "Gagal",
        'data'      => []
    ]);
}

/** Insert Demo */
$demoData = $createDemo['data'];
$insertDemo = Database::insert("tb_racc", [
    'ACC_MBR' => $userData['MBR_ID'],
    'ACC_DERE' => 2,
    'ACC_TYPE' => $demoData['type'],
    'ACC_LOGIN' => $demoData['login'],
    'ACC_PASS' => $demoData['password'],
    'ACC_INVESTOR' => $demoData['investor'],
    'ACC_PASSPHONE' => $demoData['passphone'],
    'ACC_INITIALMARGIN' => MetatraderFactory::$initMarginDemo,
    'ACC_FULLNAME' => $userData['MBR_NAME'],
    'ACC_DATETIME' => date("Y-m-d H:i:s"),
]);

/** Send Notification Email */
$emailData = [
    "subject"       => "Demo Account Information ". ProfilePerusahaan::get()['COMPANY_NAME'] ." ".date('Y-m-d H:i:s'),
    "name"          => $userData["MBR_NAME"],
    "login"         => $demoData['login'],
    "metaPassword"  => $demoData['password'],
    "metaInvestor"  => $demoData['investor'],
    "metaPassPhone" => $demoData['passphone'],
];

$emailSender = EmailSender::init(['email' => $userData['MBR_EMAIL'], 'name' => $userData['MBR_NAME']]);
$emailSender->useFile("create-demo", $emailData);
$send = $emailSender->send();

die("<script>alert('Verification Successfull'); location.href = '/dashboard';</script>");