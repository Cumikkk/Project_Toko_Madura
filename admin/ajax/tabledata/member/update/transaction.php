<?php

use App\Models\Helper;
$userCode = Helper::form_input($_GET['code'] ?? "");
$dt->query("
    SELECT
        td.DPWD_DATETIME,    
        td.DPWD_TYPE,
        tr.ACC_LOGIN,
        td.DPWD_RATE,
        td.DPWD_AMOUNT,
        td.DPWD_AMOUNT_SOURCE,
        td.DPWD_CURR_TO,
        td.DPWD_CURR_FROM,
        td.DPWD_STS
    FROM tb_dpwd td
    JOIN tb_racc tr ON (tr.ID_ACC = td.DPWD_RACC)
    JOIN tb_member tm ON (tm.MBR_ID = td.DPWD_MBR)
    WHERE tm.MBR_CODE = '{$userCode}'
");

$dt->hide("DPWD_AMOUNT_SOURCE");
$dt->hide("DPWD_CURR_TO");
$dt->hide("DPWD_CURR_FROM");

$dt->edit("DPWD_TYPE", fn($col): string => App\Models\Dpwd::type($col['DPWD_TYPE'])['text']);

$dt->edit("DPWD_RATE", fn($col): string => $col['DPWD_RATE']);

$dt->edit("DPWD_AMOUNT", function($col) {
    return '
        <div class="text-end">
            <p class="mb-0"><b>'.$col['DPWD_CURR_FROM'].'</b> '.Helper::formatCurrency($col['DPWD_AMOUNT_SOURCE']).'</p>
            <p class="mb-0"><b>'.$col['DPWD_CURR_TO'].'</b> '.Helper::formatCurrency($col['DPWD_AMOUNT']).'</p>
        </div>
    ';
});

$dt->edit("DPWD_STS", fn($col): string => App\Models\Dpwd::$status[ $col['DPWD_STS'] ]['html']);


echo $dt->generate()->toJson();