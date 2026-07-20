<?php

use App\Models\Dpwd;
use App\Models\Helper;

$mbrid = $user['MBR_ID'] ?? 0;
$dt->query("
    SELECT
        DPWD_DATETIME,
        DPWD_TYPE,
        DPWD_AMOUNT,
        DPWD_NOTE,
        DPWD_CURR_TO
    FROM tb_dpwd 
    WHERE tb_dpwd.DPWD_MBR = {$mbrid}
    AND DPWD_AMOUNT_SOURCE > 0
    AND DPWD_TYPE IN (".implode(",", [Dpwd::$typeNmiCommission, Dpwd::$typeRebateCommission]).")
");

$dt->hide('DPWD_CURR_TO');
$dt->edit('DPWD_TYPE', fn($ar): string => App\Models\Dpwd::type($ar['DPWD_TYPE'])['text']);
$dt->edit('DPWD_AMOUNT', fn($ar): string => Helper::formatCurrency($ar['DPWD_AMOUNT']) . " " . $ar['DPWD_CURR_TO']);

echo $dt->generate()->toJson();