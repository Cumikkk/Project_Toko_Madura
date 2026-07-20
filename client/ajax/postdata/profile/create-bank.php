<?php

use App\Factory\FileUploadFactory;
use App\Models\BankList;
use App\Models\Firebase;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\MemberBank;
use App\Models\User;
use App\Models\FileUpload;
use App\Models\ProfilePerusahaan;
use Config\Core\EmailSender;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$required = [
    'bank-name' => "Nama Bank",
    'bank-number' => "Nomor Rekening"
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "Kolom {$text} harus diisi",
            'data' => []
        ]);
    }
}

/** Check Max Bank */
$banks = User::myBank($user['MBR_ID']);
if(count($banks) >= 2) {
    JsonResponse([
        'success' => false,
        'message' => "Mencapai limit pembuatan bank",
        'data' => []
    ]);
}

/** check Nomor rekening */
$rekening = $data['bank-number'];
if(is_numeric($rekening) === FALSE || $rekening <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Nomor rekening tidak valid",
        'data' => []
    ]);
}

$sqlCheck = $db->query("SELECT ID_MBANK FROM tb_member_bank WHERE MBANK_MBR = ".$user['MBR_ID']." AND MBANK_ACCOUNT = {$rekening} AND MBANK_MBR != ".$user['MBR_ID']." LIMIT 1");
if($sqlCheck->num_rows != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Nomor Rekening sudah terdaftar",
        'data' => []
    ]);
}

/** Check Nama Bank */
$bankName = BankList::findByName($data['bank-name']);
if(!$bankName) {
    JsonResponse([
        'success' => false,
        'message' => "Nama Bank tidak valid",
        'data' => []
    ]);
}

// $uploadCoverBank = FileUploadFactory::aws()->upload_single($_FILES['imagecover'], "bank_cover");
// if(!is_array($uploadCoverBank) || !array_key_exists("filename", $uploadCoverBank)) {
//     exit(json_encode([
//         'success' => false,
//         'alert' => [
//             'title' => "Gagal",
//             'text'  => $uploadCoverBank ?? "Gagal mengunggah file dokumen pendukung",
//             'icon'  => "error"
//         ] 
//     ]));
// }

/** insert */
$insertData = [
    'MBANK_MBR' => $user['MBR_ID'],
    'MBANK_HOLDER' => $user['MBR_NAME'],
    'MBANK_NAME' => $data['bank-name'],
    'MBANK_ACCOUNT' => $rekening,
    'MBANK_STS' => MemberBank::$statusPending,
    // 'MBANK_IMG' => $uploadCoverBank['filename'],
    'MBANK_DATETIME' => date("Y-m-d H:i:s")
];

/** Check apakah upload cover bank */
if(!empty($_FILES['imagecover']) && $_FILES['imagecover']['error'] == 0) {
    $uploadCoverBank = FileUploadFactory::aws()->upload_single($_FILES['imagecover'], "bank_cover");
    if(!is_array($uploadCoverBank) || !array_key_exists("filename", $uploadCoverBank)) {
        JsonResponse([
            'success' => false,
            'message' => $uploadCoverBank ?? "Gagal mengunggah cover buku tabungan",
            'data' => []
        ]);
    }

    $insertData['MBANK_IMG'] = $uploadCoverBank['filename'];
}

$insert = Database::insert("tb_member_bank", $insertData);
if(!$insert) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal membuat bank",
        'data' => []
    ]);
}

/** Log */
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "create-bank",
    'data' => array_merge($_POST, $_FILES),
    'message' => "Send OTP code " . $user['MBR_EMAIL']
]);

$emailData = [
    'subject' => "[Internal] Pengajuan Rekening Bank oleh user ".$user['MBR_EMAIL'],
    'bankName' => $data['bank-name'],
    'bankAccount' => $rekening,
    'bankHolder' => $user['MBR_NAME'],
    'email' => $user['MBR_EMAIL']
];

$emailSender = EmailSender::init(['email' => ProfilePerusahaan::$emailDealing, 'name' => ProfilePerusahaan::$namaDealing]);
$emailSender->useFile('bank-request', $emailData);
$emailSender->useInternal();
$emailSender->send();

/** Firebase Notification */
$commentFirebase = "Pengajuan Rekening Bank oleh user {$user['MBR_EMAIL']}";
$pushOpsEvent = Firebase::pushOpsEvent('bank', 0, $commentFirebase, 'Web');

JsonResponse([
    'success' => true,
    'message' => "Berhasil membuat bank",
    'data' => []
]);