<?php

use App\Models\BankList;
use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
$bank_name = $data['bank_name'];
$bank = BankList::findByName($bank_name);
if($bank) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Bank sudah ada",
        'data'      => []
    ]);
}

$insert = Database::insert("tb_banklist", ['BANKLST_NAME' => $bank_name]);
if(!$insert) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal insert bank " . $bank['BANKLST_NAME'],
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "bank",
    'message' => "insert bank: " . $bank_name,
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "bank ".$bank_name." berhasil diinput",
    'data'      => []
]);