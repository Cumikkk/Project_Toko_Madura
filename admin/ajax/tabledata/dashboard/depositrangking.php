<?php

use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
    SELECT
        tm.MBR_NAME as FULLNAME,
        v_mt4_trades.LOGIN AS LOGIN,
        v_mt4_trades.PROFIT AS AMOUNT
    FROM tb_racc
    JOIN {$dbmetasrv}.MT4_TRADES v_mt4_trades ON(tb_racc.ACC_LOGIN = v_mt4_trades.LOGIN)
    JOIN tb_member tm ON (tm.MBR_ID = tb_racc.ACC_MBR)
    WHERE v_mt4_trades.CMD = 6
    ORDER BY AMOUNT DESC
    LIMIT 10
");
$dt->edit("AMOUNT", function($col) {
    return number_format($col['AMOUNT'], 2);
});

echo $dt->generate()->toJson();