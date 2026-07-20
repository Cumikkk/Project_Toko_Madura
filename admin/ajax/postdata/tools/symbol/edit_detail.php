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
foreach(["symbol_category", "symbol_name", "xdt"] as $req) {
    if(empty($data[ $req ])) {
        $req = str_replace("add_", "", $req);
        JsonResponse([
            'code'      => 402,
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}
$symbol_category = trim($data['symbol_category'] ?? '');
$symbol_name = trim($data['symbol_name'] ?? '');

$SYMBOL_IDCHK = Symbols::findById($data["xdt"]);
if(!$SYMBOL_IDCHK) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Symbol ID not found",
        'data'      => []
    ]);
}


$symbol = Symbols::findByName($symbol_name);
if($symbol && MD5(MD5($SYMBOL_IDCHK["ID_SYM"])) != $data["xdt"]) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Symbol sudah ada",
        'data'      => []
    ]);
}

$update = Database::update("tb_symbol", ['ID_SYMCAT' => $symbol_category, 'SYM_NAME' => $symbol_name], ["ID_SYM" => $SYMBOL_IDCHK["ID_SYM"]]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal update Symbol " . $symbol_name,
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "Symbol",
    'message' => "update Symbol: " . $symbol_name,
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Symbol ".$symbol_name." berhasil diupdate",
    'data'      => []
]);