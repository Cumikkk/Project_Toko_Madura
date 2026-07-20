<?php

use App\Models\Account;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\Regol;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$suffix = Helper::form_input($_POST['account-type'] ?? "-");
$raccType = Account::checkAccountSuffix($suffix);
if(empty($raccType)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Product",
        'data' => []
    ]);
}

/** Cek apakah produk compatibel dengan akunnya */
if(!empty($user['MBR_SUFFIX'])) {
    if($user['MBR_SUFFIX'] != $suffix) {
        JsonResponse([
            'success' => false,
            'message' => "Jenis akun tidak valid",
            'data' => []
        ]);
    }
}

if(!empty($user['MBR_SUFFIX_EXCLUDE'])) {
    $explode = explode(",", $user['MBR_SUFFIX_EXCLUDE']);
    if(in_array($suffix, $explode)) {
        JsonResponse([
            'success' => false,
            'message' => "Jenis akun tidak valid",
            'data' => []
        ]);
    }
}

/** Check max account */
$microAcc = [];
$realAcc = Account ::getAvailableProduct(md5(md5($user['MBR_ID'])), "real");
foreach($realAcc as $acc) {
    if(strtolower($acc['RTYPE_TYPE']) == "micro") {
        $microAcc[] = $acc;
    }
}
    
if(count($realAcc) >= $user['MBR_ACCMAX']) {
    JsonResponse([
        'success' => false,
        'message' => "Jenis akun tidak valid",
        'data' => []
    ]);
}

if(strtoupper($raccType['RTYPE_TYPE']) == "MICRO") {
    if(count($microAcc) >= $user['MBR_ACCMAX_MICRO']) {
        JsonResponse([
            'success' => false,
            'message' => "Jenis akun tidak valid",
            'data' => []
        ]);
    }
}

/** Get Progress Real Account */
$progressAccount = Account::getProgressRealAccount($userid);
if(empty($progressAccount)) {
    /** Jika sebelumnya sudah punya akun, dapat diduplicate */
    if(!empty(Account::getLastAccount($userid))) {
        $duplicate = Account::duplicateLastAccount($userid);
        if(empty($duplicate) || !is_array($duplicate)) {
            JsonResponse([
                'success' => false,
                'message' => "Status salinan tidak valid",
                'data' => []
            ]);
        }

    }else {
        /** Insert row baru, Jika belum punya akun sama sekali / baru pertama create akun */
        $insert = Database::insert("tb_racc", [
            'ACC_MBR'   => $user['MBR_ID'],
            'ACC_TYPE'  => $raccType['ID_RTYPE'],
            'ACC_DERE'  => 1,
            'ACC_LOGIN' => 0,
            'ACC_STS'   => 0
        ]);

        if(empty($insert)) {
            JsonResponse([
                'success' => false,
                'message' => "Gagal membuat akun",
                'data' => []
            ]);
        }
    }
}

/** Reload Progress real account */
$progressAccount = Account::getProgressRealAccount($userid);

/** Check Status */
Regol::isAllowToEdit($progressAccount['ACC_STS']);

/** Update Type */
if($progressAccount['ACC_TYPE'] != $raccType['ID_RTYPE']) {
    $update = Database::update("tb_racc", ['ACC_TYPE' => $raccType['ID_RTYPE']], ['ID_ACC' => $progressAccount['ID_ACC']]);
    if(!$update) {
        JsonResponse([
            'success' => false,
            'message' => "Perbarui Jenis Akun Gagal",
            'data' => []
        ]);
    }
}

Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "create-account",
    'message' => "Progress Real Account (Account Type)",
    'data' => $data
]);

JsonResponse([
    'success' => true,
    'message' => "Pilih Jenis Akun Sukses",
    'data' => [
        'redirect' => '/account/create?page=profile-perusahaan'
    ]
]);