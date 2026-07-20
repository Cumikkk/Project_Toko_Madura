<?php

use App\Models\Helper;
use App\Models\MemberBank;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'success' => false,
        'message' => "Authorization Failed",
        'data' => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['type', 'id'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$req} is required",
            'data' => []
        ]);
    }
}

/** check id */
$bank = MemberBank::findByIdHash($data['id']);
if(!$bank) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid ID",
        'data' => []
    ]);
}

$userdata = User::findByMemberId($bank['MBANK_MBR']);
if(!$userdata) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Userdata",
        'data' => []
    ]);
} 

/** check type */
if(!in_array(strtolower($data['type']), ['accept', 'reject'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

$note = $data['note'] ?? "";
if($data['type'] == "reject" && empty($note)) {
    JsonResponse([
        'success' => false,
        'message' => "Note is required",
        'data' => []
    ]);
}

$status = ($data['type'] == "accept")? MemberBank::$statusAccepted : MemberBank::$statusRejected;
$update = Database::update("tb_member_bank", ['MBANK_STS' => $status, 'MBANK_REJECT_NOTE' => $note], ['ID_MBANK' => $bank['ID_MBANK']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal",
        'data' => []
    ]);
}

/** Send email */
switch($status) {
    case MemberBank::$statusAccepted:
        $emailData = [
            'subject' => "Pengajuan Rekening Bank Disetujui",
            'bankName'  => $bank['MBANK_NAME'],
            'bankAccount'  => $bank['MBANK_ACCOUNT'],
            'bankHolder'  => $bank['MBANK_HOLDER'],
        ];

        $emailSender = EmailSender::init(['email' => $userdata['MBR_EMAIL'], 'name' => $userdata['MBR_NAME']]);
        $emailSender->useFile("bank-success", $emailData);
        $emailSender->useInternal();
        $emailSender->send();
        break;

    case MemberBank::$statusRejected:
        $emailData = [
            'subject' => "Pengajuan Rekening Bank Ditolak",
            'bankName'  => $bank['MBANK_NAME'],
            'bankAccount'  => $bank['MBANK_ACCOUNT'],
            'bankHolder'  => $bank['MBANK_HOLDER'],
        ];

        $emailSender = EmailSender::init(['email' => $userdata['MBR_EMAIL'], 'name' => $userdata['MBR_NAME']]);
        $emailSender->useFile("bank-reject", $emailData);
        $emailSender->useInternal();
        $emailSender->send();
        break;
        
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil " . ucwords($data['type']),
    'data' => []
]);