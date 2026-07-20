<?php

use App\Models\Dpwd;

$memberId = $user['MBR_ID'];
$dt->query("
    SELECT 
        d.DPWD_DATETIME as `datetime`,
        d.DPWD_TYPE as type,
        d.DPWD_NOTE as description,
        d.DPWD_AMOUNT as amount,
        d.DPWD_AMOUNT_SOURCE as amount_source,
        d.DPWD_STS as `status`
    FROM tb_dpwd as d
    WHERE DPWD_MBR = {$memberId}
    AND DPWD_TYPE IN (".Dpwd::$typeRebateCommission.", ".Dpwd::$typeWithdrawalCommission.")
");

$dt->hide('amount_source');
$dt->edit('type', fn($ar): string => Dpwd::type($ar['type'])['text']);
$dt->edit('status', function($ar) {
    $status = Dpwd::status($ar['status']);
    return '<span class="badge bg-'.$status['badge'].'">'.$status['text_en'].'</span>';
});

$dt->edit('amount', function($ar) {
    return ($ar['type'] == Dpwd::$typeRebateCommission)? $ar['amount'] : $ar['amount_source'];  
});

echo $dt->generate()->toJson();