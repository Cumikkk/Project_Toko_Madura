<?php

use App\Models\Helper;

$userCode = Helper::form_input($_GET['code'] ?? "");
$dt->query("
    SELECT 
        ACC_DATETIME,
        ACC_DERE,
        ACC_LOGIN,
        mt5u.LEVERAGE,
        trt.RTYPE_TYPE,
        trt.RTYPE_KOMISI,
        trt.RTYPE_CURR,
        IF(trt.RTYPE_ISFLOATING, 'Floating', (trt.RTYPE_RATE / 1000)) as RATE,
        mt5u.BALANCE,
        mt5u.MARGIN_FREE
    FROM tb_racc tr
    JOIN tb_member tm ON (tm.MBR_ID = tr.ACC_MBR)
    JOIN tb_racctype trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
    JOIN mt4_users mt5u ON (mt5u.LOGIN = tr.ACC_LOGIN)
    WHERE tm.MBR_CODE = '{$userCode}'
    GROUP BY tr.ACC_LOGIN
");

$dt->hide("RTYPE_KOMISI");
$dt->hide("RTYPE_CURR");
$dt->hide("RATE");

$dt->edit("ACC_DERE", function($col) {
    switch($col['ACC_DERE']) {
        case 1: return '<div class="text-center"><span class="badge bg-success px-2">Live</span></div>';
        case 2: return '<div class="text-center"><span class="badge bg-danger">Demo</span></div>';
        default: return "-";
    }
});

$dt->edit("ACC_LOGIN", function($col) {
    return '<div class="text-end">'.$col['ACC_LOGIN'].'</div>';
});

$dt->edit("LEVERAGE", function($col) {
    return '<div class="text-end">1:'.Helper::formatCurrency($col['LEVERAGE']).'</div>';
});

$dt->edit("RTYPE_TYPE", function($col) {
    return implode("/", [$col['RTYPE_TYPE'], $col['RATE'], $col['RTYPE_KOMISI']]);
});

$dt->edit("BALANCE", function($col) {
    return '<div class="text-end">$'.Helper::formatCurrency($col['BALANCE']).'</div>';
});

$dt->edit("MARGIN_FREE", function($col) {
    return '<div class="text-end">$'.Helper::formatCurrency($col['MARGIN_FREE']).'</div>';
});

echo $dt->generate()->toJson(); 