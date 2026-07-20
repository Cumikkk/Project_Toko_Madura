<?php

use App\Factory\FileUploadFactory;
use App\Models\BankList;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\MemberBank;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use App\Models\Firebase;
use Config\Core\Database;
use Config\Core\EmailSender;

$data = Helper::getSafeInput($_POST);
$required = [
    'id' => "ID bank",
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

/** check id */
$bank = User::myBank($user['MBR_ID'], $data['id']);
if(!$bank) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid ID",
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

/** Update Data */
$updateData = [
    'MBANK_HOLDER' => $user['MBR_NAME'],
    'MBANK_NAME' => $data['bank-name'],
    'MBANK_ACCOUNT' => $rekening,
    'MBANK_STS' => MemberBank::$statusPending
];

/** Upload cover bank (Opsional) */
if(!empty($_FILES['imagecover']) && $_FILES['imagecover']['error'] == 0) {
    $uploadCoverBank = FileUploadFactory::aws()->upload_single($_FILES['imagecover'], "bank_cover");
    if(!is_array($uploadCoverBank) || !array_key_exists("filename", $uploadCoverBank)) {
        JsonResponse([
            'success' => false,
            'message' => $uploadCoverBank ?? "Gagal mengunggah cover buku tabungan",
            'data' => []
        ]);
    }

    $updateData['MBANK_IMG'] = $uploadCoverBank['filename'];
}

$update = Database::update("tb_member_bank", $updateData, ['ID_MBANK' => $bank['ID_MBANK']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui bank",
        'data' => []
    ]);
}

$emailData = [
    'subject' => "[Internal] Pengajuan Pembaruan Data Rekening Bank oleh user ".$user['MBR_EMAIL'],
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
$commentFirebase = "Pengajuan Pembaruan Data Rekening Bank oleh user {$user['MBR_EMAIL']}";
$pushOpsEvent = Firebase::pushOpsEvent('bank', 0, $commentFirebase, 'Web');

/** Log */
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "update-bank",
    'message' => "Update bank ",
    'data' => array_merge(
        $_POST, 
        $_FILES,
        ['before' => $bank],
        ['after' => $updateData]
    ),
]);

JsonResponse([
    'success' => true,
    'message' => "Bank berhasil diperbarui",
    'data' => []
]);