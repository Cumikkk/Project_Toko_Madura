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
$idb = $data['id'];
$bank = BankList::findById($idb);
if(!$bank) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Bank tidak ditemukan",
        'data'      => []
    ]);
}

$delete = Database::delete("tb_banklist", ['ID_BANKLST' => $idb]);
if(!$delete) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal hapus bank " . $bank['BANKLST_NAME']." - " . $idb,
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "bank",
    'message' => "hapus bank: " . $bank['BANKLST_NAME'],
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "bank ".$bank['BANKLST_NAME']." berhasil dihapus",
    'data'      => []
]);
?>