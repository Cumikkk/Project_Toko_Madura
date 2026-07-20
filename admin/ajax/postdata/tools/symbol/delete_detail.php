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
foreach(["xdt"] as $req) {
    if(empty($data[ $req ])) {
        $req = str_replace("add_", "", $req);
        JsonResponse([
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}

$SYMBOL_IDCHK = Symbols::findById($data["xdt"]);
if(!$SYMBOL_IDCHK) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Symbol ID not found",
        'data'      => []
    ]);
}
$symbol_name = $SYMBOL_IDCHK["SYM_NAME"];

$delete = Database::delete("tb_symbol", ["ID_SYM" => $SYMBOL_IDCHK["ID_SYM"]]);
if(!$delete) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal delete Symbol " . $symbol_name,
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "Symbol",
    'message' => "delete Symbol: " . $symbol_name,
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Symbol ".$symbol_name." berhasil dihapus",
    'data'      => []
]);