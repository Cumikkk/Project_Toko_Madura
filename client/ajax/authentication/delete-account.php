<?php

use App\Factory\UserOtpFactory;
use App\Models\Account;
use App\Models\Firebase;
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

$otp = Helper::form_input($_POST['otp']);
if(empty($otp) || is_numeric($otp) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Kode OTP diperlukan",
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

/** meta accounts */
$accounts = Account::myAccount($userData['MBR_ID']);

/** user Banks */
$userBanks = MemberBank::list($userData['MBR_ID']);

/** check requirement delete account */
if(!User::checkReqDeleteAccount($userData['MBR_ID'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Request",
        'data' => []
    ]);
}

/** Initialize account deleted */
$accountDeleted = [];
foreach($accounts as $acc) {
    $accountDeleted[] = [
        'login' => $acc['ACC_LOGIN'],
        'free_margin' => $acc['MARGIN_FREE'] ??= 0
    ];
}

/** initialize bank deleted */
$bankDeleted = [];
foreach($userBanks as $bank) {
    $bankDeleted[] = $bank['MBANK_ACCOUNT'];
}

$STORED_DATA = [
    'DLTACC_MBR' => $userData['MBR_ID'],
    'DLTACC_NAMLENG' => $userData['MBR_NAME']
];

$CHCK_VAR = [
    'DLTACC_ACCOUNT' => implode(", ", array_column($accountDeleted, "login")),
    'DLTACC_LST_EQT' => implode(", ", array_column($accountDeleted, "login")),
    'DLTACC_NOREK_NSBH' => implode(", ", $bankDeleted),
    'DLTACC_NOIDT' => $userData["MBR_NO_IDT"],
    'DLTACC_EMAIL' => $userData["MBR_EMAIL"],
    'DLTACC_NOTELP' => $userData["MBR_PHONE"]
];

foreach ($CHCK_VAR as $key => $value) {
    if(!empty($value)){
        $STORED_DATA["$key"] = $value;
    }
}


/** use otp */
$useOtp = UserOtpFactory::useOtp($otp, $userData['MBR_ID']);
if($useOtp !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $useOtp ?? "Invalid Request",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** insert delete account */
$insert = Database::insert("tb_dlt_account", $STORED_DATA);
if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal insert data",
        'data' => []
    ]);
}

/** admin Email Notification  */
$emailData = [
    'subject' => "[Internal] Penghapusan User ".date("d/m/Y"),
    'nama'    => $userData['MBR_NAME'],
    'email'   => $userData['MBR_EMAIL']
];

$emailSender = EmailSender::init(['email' => ProfilePerusahaan::$emailDealing, 'name' => ProfilePerusahaan::$namaDealing]);
$emailSender->useFile("otp-delete-admin-notif", $emailData);
$emailSender->useInternal();
$send = $emailSender->send();

// $pushOpsEvent = Firebase::pushOpsEvent('internal_transfer', 0, 0, 'Web');

$db->commit();
Logger::client_log([
    'mbrid' => $userData['MBR_ID'],
    'module' => "delete-account",
    'data' => $STORED_DATA,
    'message' => "Request Delete Account From email: " . $data['email']
]);

JsonResponse([
    'success' => true,
    'message' => "Permintaan penghapusan akun Anda telah kami terima dan sedang diproses. Jika ini bukan permintaan Anda atau memerlukan bantuan, silakan hubungi Tim Dukungan.",
    'data' => []
]);