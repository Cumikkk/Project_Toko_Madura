<?php

$sqlGetAccounts = $db->query("
    SELECT 
        tr.ACC_LOGIN,
        IF(tr.ACC_DERE = 1, 'Real', 'Demo') as ACC_DERE,
        trt.RTYPE_SERVER,
        trt.RTYPE_META_CURR,
        mt4u.BALANCE,
        mt4u.CREDIT,
        mt4u.EQUITY,
        mt4u.MARGIN,
        mt4u.MARGIN_FREE,
        mt4u.MARGIN_LEVEL,
        mt4u.LEVERAGE
    FROM tb_racc as tr
    JOIN tb_racctype as trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
    JOIN mt4_users as mt4u ON (mt4u.LOGIN = tr.ACC_LOGIN AND mt4u.account_type = 'real')
    WHERE tr.ACC_MBR = {$user['MBR_ID']} 
    AND tr.ACC_STS = -1
");

$accounts = [];
if($sqlGetAccounts && $sqlGetAccounts->num_rows > 0) {
    foreach($sqlGetAccounts->fetch_all(MYSQLI_ASSOC) as $row) {
        $accounts[] = [
            'login' => (int) $row['ACC_LOGIN'],
            'type' => $row['ACC_DERE'],
            'leverage' => (int) $row['LEVERAGE'],
            'currency' => $row['RTYPE_META_CURR'],
            'balance' => (float) $row['BALANCE'],
            'credit' => (float) $row['CREDIT'],
            'equity' => (float) $row['EQUITY'],
            'margin_free' => (float) $row['MARGIN_FREE'],
            'margin' => (float) $row['MARGIN'],
            'margin_level' => (float) $row['MARGIN_LEVEL']
        ];
    }
}

JsonResponse([
    'success' => true,
    'message' => 'Accounts fetched successfully',
    'data' => $accounts
]);