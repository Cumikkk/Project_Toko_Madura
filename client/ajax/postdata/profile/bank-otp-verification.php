<?php

use App\Models\Helper;
use App\Models\User;
use App\Models\Firebase;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$required = [
    'idotp' => "ID Confirmation",
    'otp-bank' => "OTP Confirmation"
];

$idotp = $data['idotp'];
$otpcode = $data['otp-bank'];

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
$bank = User::myBank($user['MBR_ID'], $idotp);
if(!$bank) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid ID",
        'data' => []
    ]);
}

$sqlCheck = $db->query("SELECT ID_MBANK FROM tb_member_bank WHERE MD5(MD5(ID_MBANK)) = '".$idotp."' AND MBANK_STS = 0 AND MBANK_OTP = '".$otpcode."' LIMIT 1");
if($sqlCheck->num_rows == 0) {
    exit(json_encode([
        'success' => false,
        'alert' => [
            'title' => "Gagal",
            'text'  => "OTP Tidak valid",
            'icon'  => "error"
        ] 
    ]));
}

/** check expired */
if(empty($bank['MBANK_OTP_EXPIRED']) || strtotime($bank['MBANK_OTP_EXPIRED']) < strtotime("now")) {
    JsonResponse([
        'success' => false,
        'message' => "Kode OTP kadaluarsa",
        'data' => []
    ]);
}

$update = Database::update("tb_member_bank", ['MBANK_STS' => -1], [
    'MD5(MD5(ID_MBANK))' => $idotp,
    'MBANK_OTP' => $otpcode,
    'MBANK_STS' => 0
]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui data",
        'data' => []
    ]);
}

$pushOpsEvent = Firebase::pushOpsEvent('internal_transfer', 0, '-', 'Web');

JsonResponse([
    'success' => true,
    'message' => "Verifikasi bank successfull",
    'data' => []
]);