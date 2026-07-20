<?php

use Config\Core\SystemInfo;

$mbrid = $user['MBR_ID'];
$dbMetaReal = SystemInfo::app('DB_METALIVE');
$dbMetaDemo = SystemInfo::app('DB_METADEMO');

$dt->query("
    SELECT 
        tr.ACC_LOGIN,
        trt.RTYPE_LEVERAGE,
        mt5u.MARGIN_FREE
    FROM tb_racc tr
    JOIN tb_racctype trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
    JOIN (
        SELECT 
            LOGIN,
            MARGIN_FREE
        FROM.mt4_users
    ) mt4u ON (mt4u.LOGIN = tr.ACC_LOGIN)
    WHERE tr.ACC_MBR = {$mbrid}
    AND tr.ACC_STS = -1
    AND tr.ACC_LOGIN != '0'
");

$dt->edit('MARGIN_FREE', fn($col) :string => App\Models\Helper::formatCurrency($col['MARGIN_FREE']) . " USD");
$dt->add('ACTION', fn($col) :string => '<a class="btn btn-sm btn-success text-white btn-update" data-login="'.$col['ACC_LOGIN'].'"><i class="fas fa-lock"></i></a>');

echo $dt->generate()->toJson();