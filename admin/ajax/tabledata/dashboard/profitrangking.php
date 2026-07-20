<?php

use Config\Core\SystemInfo;
$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
    SELECT
        tm.MBR_NAME as FULLNAME,
        v_mt4_trades.LOGIN AS LOGIN,
        IFNULL(SUM(v_mt4_trades.PROFIT), 0) AS AMOUNT
    FROM tb_racc
    JOIN {$dbmetasrv}.MT4_TRADES v_mt4_trades ON(v_mt4_trades.LOGIN = tb_racc.ACC_LOGIN)
    JOIN tb_member tm ON (tm.MBR_ID = tb_racc.ACC_MBR)
    WHERE v_mt4_trades.CMD IN (0, 1)
    AND v_mt4_trades.PROFIT > 0
    GROUP BY v_mt4_trades.LOGIN
    ORDER BY AMOUNT DESC
    LIMIT 10
");
$dt->edit("AMOUNT", function($col) {
    return number_format($col['AMOUNT'], 2);
});

echo $dt->generate()->toJson();