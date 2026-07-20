<?php

use App\Models\Account;
use App\Models\Helper;

$data = Helper::getSafeInput($_POST);
// if(empty($data['account'])) {
//     JsonResponse([
//         'success' => false,
//         'message' => "Akun diperlukan",
//         'data' => []
//     ]);
// }

// $account = Account::realAccountDetail($data['account']);
// if(!$account) {
//     JsonResponse([
//         'success' => false,
//         'message' => "Invalid Account",
//         'data' => []
//     ]);
// }

if(empty($data['currency'])) {
    JsonResponse([
        'success' => false,
        'message' => "Currency is required",
        'data' => []
    ]);
}

if(!in_array($data['currency'], ['IDR', 'USD'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Currency",
        'data' => []
    ]);
}

$result = [];
$sqlGetBank = $db->query("SELECT * FROM tb_bankadm WHERE BKADM_CURR = '".$data['currency']."'");
foreach($sqlGetBank->fetch_all(MYSQLI_ASSOC) as $bank) {
    $result[] = [
        'id' => md5(md5($bank['ID_BKADM'])),
        'detail' => implode(" / ", [$bank['BKADM_NAME'], $bank['BKADM_HOLDER'], $bank['BKADM_ACCOUNT']]),
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => $result
]);