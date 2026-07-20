<?php

use Config\Core\SystemInfo;
$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
    SELECT
        v_mt4_trades.SYMBOL AS SYMBOL,
        IFNULL(SUM(v_mt4_trades.PROFIT), 0) AS AMOUNT
    FROM {$dbmetasrv}.MT4_TRADES v_mt4_trades
    WHERE v_mt4_trades.CMD IN (0, 1)
    GROUP BY v_mt4_trades.SYMBOL
    ORDER BY AMOUNT DESC
    LIMIT 10
");
$dt->edit("AMOUNT", function($col) {
    return number_format($col['AMOUNT'], 2);
});

echo $dt->generate()->toJson();