<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query('
    SELECT
        MT4_TRADES.OPEN_TIME AS `datetime`,
        MT4_TRADES.LOGIN AS login,
        tb_racc.ACC_FULLNAME AS fullname,
        MT4_TRADES.TICKET AS ticket,
        IF(MT4_TRADES.CMD = 0, "Buy", "Sell") AS `type`,
        MT4_TRADES.SYMBOL AS symbol,
        ROUND(MT4_TRADES.VOLUME/100, 2) AS volume,
        FORMAT(MT4_TRADES.SL, MT4_PRICES.DIGITS) AS sl,
        FORMAT(MT4_TRADES.TP, MT4_PRICES.DIGITS) AS tp,
        FORMAT(MT4_TRADES.OPEN_PRICE, MT4_PRICES.DIGITS) AS price
    FROM '.$dbmetasrv.'.MT4_TRADES
    JOIN '.$dbmetasrv.'.MT4_PRICES ON (MT4_PRICES.SYMBOL = MT4_TRADES.SYMBOL)
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = MT4_TRADES.login)
    WHERE MT4_TRADES.CMD IN(0, 1)
    AND DATE(MT4_TRADES.CLOSE_TIME) = DATE("1970-01-01")
    AND tb_racc.ACC_DERE = 1
');

echo $dt->generate()->toJson();
?>