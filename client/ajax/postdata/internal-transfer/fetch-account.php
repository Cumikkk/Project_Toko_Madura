<?php

use App\Models\Account;
use App\Models\Helper;

/** Check account */
$fromAccount = Helper::form_input($_POST['from'] ?? "");
$from = Account::realAccountDetail_byLogin($fromAccount);
if(!$from) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

$result = [];
$mbrid = $user['MBR_ID'];
$rate = $from['RTYPE_RATE'];
$sqlGet = $db->query("
    SELECT 
        tr.ACC_LOGIN,
        mt4u.MARGIN_FREE
    FROM tb_racc tr
    JOIN tb_racctype trt ON (trt.ID_RTYPE = tr.ACC_TYPE) 
    JOIN mt4_users mt4u ON (mt4u.LOGIN = tr.ACC_LOGIN AND mt4u.account_type = 'real')
    WHERE tr.ACC_MBR = {$mbrid} 
    AND trt.RTYPE_RATE = {$rate} 
    AND tr.ACC_LOGIN != '$fromAccount' 
    AND tr.ACC_DERE = 1 
    AND tr.ACC_STS = -1 
    AND tr.ACC_LOGIN != 0
");

if($sqlGet->num_rows != 0) {
    foreach($sqlGet->fetch_all(MYSQLI_ASSOC) as $asoc) {
        $result[] = [
            'login' => $asoc['ACC_LOGIN'],
            'balance' => Helper::formatCurrency($asoc['MARGIN_FREE']). " USD"
        ];
    }
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => $result
]);