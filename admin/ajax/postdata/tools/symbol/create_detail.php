<?php

use App\Models\Helper;
use App\Models\Logger;
use App\Models\Symbols;
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
$symbol_category = trim($data['symbol_category'] ?? '');
$symbol_name = trim($data['symbol_name'] ?? '');

$symbol = Symbols::findByName($symbol_name);
if($symbol) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Symbol sudah ada",
        'data'      => []
    ]);
}

$insert = Database::insert("tb_symbol", ['ID_SYMCAT' => $symbol_category, 'SYM_NAME' => $symbol_name]);
if(!$insert) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal insert Symbol " . $symbol_name,
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "Symbol",
    'message' => "insert Symbol: " . $symbol_name,
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Symbol ".$symbol_name." berhasil diinput",
    'data'      => []
]);