<?php

use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');

$dt->query("
    SELECT
        tm.MBR_NAME as FULLNAME,
        v_mt4_users.LOGIN,
        v_mt4_users.BALANCE AS AMOUNT
    FROM tb_racc
    JOIN {$dbmetasrv}.MT4_USERS v_mt4_users ON(tb_racc.ACC_LOGIN = v_mt4_users.LOGIN)
    JOIN tb_member tm ON (tm.MBR_ID = tb_racc.ACC_MBR)
    ORDER BY AMOUNT DESC
    LIMIT 10
");
$dt->edit("AMOUNT", function($col) {
    return number_format($col['AMOUNT'], 2);
});

echo $dt->generate()->toJson();