<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
    SELECT
        MT4_TRADES.CLOSE_TIME AS `time`,
        MT4_TRADES.LOGIN AS login,
        tb_racc.ACC_FULLNAME AS fullname,
        MT4_TRADES.TICKET AS deal_id,
        MT4_TRADES.SYMBOL AS symbol,
        IF(MT4_TRADES.CMD = 0, 'Buy', 'Sell') AS `action`,
        FORMAT(MT4_TRADES.OPEN_PRICE, MT4_PRICES.DIGITS) AS price,
        ROUND(MT4_TRADES.VOLUME/100, 2) AS volume,
        FORMAT(MT4_TRADES.SL, MT4_PRICES.DIGITS) AS price_sl,
        FORMAT(MT4_TRADES.TP, MT4_PRICES.DIGITS) AS price_tp,
        ROUND(MT4_TRADES.COMMISSION, 2) AS commission,
        ROUND(MT4_TRADES.`SWAPS`, 2) AS `storage`,
        ROUND(MT4_TRADES.PROFIT, 2) AS profit
    FROM ".$dbmetasrv.".MT4_TRADES
    JOIN ".$dbmetasrv.".MT4_PRICES ON (MT4_PRICES.SYMBOL = MT4_TRADES.SYMBOL)
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = MT4_TRADES.LOGIN)
    WHERE MT4_TRADES.CMD IN(0, 1)
    AND DATE(MT4_TRADES.CLOSE_TIME) > DATE('1970-01-01')
    AND tb_racc.ACC_DERE = 1
");

echo $dt->generate()->toJson();
?>