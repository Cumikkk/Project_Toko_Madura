<?php
use App\Models\Helper;
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$data = Helper::getSafeInput($_GET);

$enddate   = isset($data['enddate']) ? (int)$data['enddate'] : time();
$startdate = isset($data['startdate']) ? (int)$data['startdate'] : strtotime('-7 days', $enddate);

$dt->query("
    SELECT
        MT4_TRADES.LOGIN AS login,
        MT4_USERS.`NAME` AS `name`,
        MT4_USERS.`GROUP` AS `group_data`,
        SUM(IF(TIMESTAMPDIFF(SECOND, MT4_TRADES.OPEN_TIME, MT4_TRADES.CLOSE_TIME) < 30, 1, 0)) AS ISSCALPER,
        COUNT(MT4_TRADES.TICKET) AS ALLSCALPER,
        IFNULL(SUM(IF(TIMESTAMPDIFF(SECOND, MT4_TRADES.OPEN_TIME, MT4_TRADES.CLOSE_TIME) < 30, 1, 0)) / COUNT(MT4_TRADES.TICKET) * 100, 0) AS PERCENT_TRADE,
        SUM(IF(TIMESTAMPDIFF(SECOND, MT4_TRADES.OPEN_TIME, MT4_TRADES.CLOSE_TIME) < 30, MT4_TRADES.PROFIT, 0)) AS profit,
        MD5(MD5(MT4_TRADES.LOGIN)) AS `DATA`
    FROM {$dbmetasrv}.MT4_TRADES
    JOIN {$dbmetasrv}.MT4_USERS ON(MT4_USERS.LOGIN = MT4_TRADES.LOGIN)
    WHERE DATE(MT4_TRADES.CLOSE_TIME) > DATE('1970-01-01')
    AND UNIX_TIMESTAMP(MT4_TRADES.OPEN_TIME) >= ".$startdate."
    AND UNIX_TIMESTAMP(MT4_TRADES.OPEN_TIME) < (".$enddate + (2 * 86400).")
    AND MT4_TRADES.CMD IN (0, 1)
    GROUP BY MT4_USERS.LOGIN
");

echo $dt->generate()->toJson();