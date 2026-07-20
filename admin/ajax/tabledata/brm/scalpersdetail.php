<?php
use App\Models\Helper;
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$data = Helper::getSafeInput($_GET);



    // WHERE MD5(MD5(LOGIN)) = '".$data['login']."'
    // AND TIME_TO_SEC(TIMEDIFF(v_mt5_trades.CLOSE_TIME, v_mt5_trades.OPEN_TIME)) <= 30
    // AND DATE(v_mt5_trades.OPEN_TIME) >= DATE(FROM_UNIXTIME('".$data['startdate']."')) 
    // AND DATE(v_mt5_trades.OPEN_TIME) < DATE_ADD(DATE(FROM_UNIXTIME('".$data['enddate']."')), INTERVAL 2 DAY)
$dt->query("
    SELECT
        v_mt5_deals.position_id,
        SUM(IF(v_mt5_deals.`entry` = 0, v_mt5_deals.volume/1000, 0)) AS total_in,
        SUM(IF(v_mt5_deals.`entry` = 1, v_mt5_deals.volume/1000, 0)) AS total_out,
        v_mt5_deals.symbol,
        FROM_UNIXTIME(MIN(v_mt5_deals.`time`)) AS open_time,
        FROM_UNIXTIME(MAX(v_mt5_deals.`time`)) AS close_time,
        TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(MIN(v_mt5_deals.`time`)), FROM_UNIXTIME(MAX(v_mt5_deals.`time`))) AS difference,
        SUM(IF(v_mt5_deals.`entry` = 1, v_mt5_deals.volume, 0)) AS sum_volume,
        ROUND(SUM(v_mt5_deals.profit), v_mt5_deals.digits_currency) AS sum_profit
    FROM {$dbmetasrv}.mt5_deals AS v_mt5_deals
    WHERE v_mt5_deals.`action` IN (0, 1)
    AND MD5(MD5(v_mt5_deals.login)) = '".$data['login']."'
    AND v_mt5_deals.`time` > ".$data['startdate']."
    AND v_mt5_deals.`time` < (".$data['enddate']." + (2 * 86400))
    GROUP BY v_mt5_deals.login, v_mt5_deals.position_id
    HAVING total_in = total_out
    AND difference <= 30
");

echo $dt->generate()->toJson();