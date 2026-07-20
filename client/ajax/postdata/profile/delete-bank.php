<?php

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
    'x' => "ID Bank"
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
$bank = User::myBank($user['MBR_ID'], $data['x']);
if(!$bank) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid ID",
        'data' => []
    ]);
}

/** SQL Execute */
$delete = Database::delete("tb_member_bank", ['ID_MBANK' => $bank['ID_MBANK']]);
if(!$delete) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal menghapus bank",
        'data' => []
    ]);
}

/** Log */
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "delete-bank",
    'message' => "Delete bank ",
    'data' => array_merge(
        $_POST,
        ['bank_info' => $bank]
    ),
]);

JsonResponse([
    'success' => true,
    'message' => "Bank berhasil diperbarui",
    'data' => []
]);