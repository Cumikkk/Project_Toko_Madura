<?php

use App\Models\Dpwd;
use App\Models\Helper;

$mbrid = $user['MBR_ID'] ?? 0;
$dt->query("
    SELECT
        DPWD_DATETIME,
        DPWD_TYPE,
        DPWD_AMOUNT_SOURCE,
        DPWD_STS,
        DPWD_NOTE,
        DPWD_CURR_FROM
    FROM tb_dpwd 
    WHERE tb_dpwd.DPWD_MBR = {$mbrid}
    AND DPWD_AMOUNT_SOURCE < 0
    AND DPWD_TYPE IN (".implode(",", [Dpwd::$typeNmiCommission, Dpwd::$typeRebateCommission]).")
");

$dt->hide('DPWD_CURR_FROM');
$dt->edit('DPWD_TYPE', fn($ar): string => App\Models\Dpwd::type($ar['DPWD_TYPE'])['text']);
$dt->edit('DPWD_AMOUNT_SOURCE', fn($ar): string => Helper::formatCurrency($ar['DPWD_AMOUNT_SOURCE']) . " " . $ar['DPWD_CURR_FROM']);
$dt->edit("DPWD_STS", fn($col): string => App\Models\Dpwd::$status[ $col['DPWD_STS'] ]['html']);

echo $dt->generate()->toJson();