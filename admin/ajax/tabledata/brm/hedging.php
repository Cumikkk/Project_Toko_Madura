<?php
use App\Models\Helper;
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$data = Helper::getSafeInput($_GET);

$enddate   = isset($data['enddate']) ? (int)$data['enddate'] : time();
$startdate = isset($data['startdate']) ? (int)$data['startdate'] : strtotime('-7 days', $enddate);


$dt->query('
    SELECT
        n0.login AS LOGIN,
        tb_member.MBR_NAME AS `name`,
        CONCAT(n0.position_id, "_", n1.position_id) AS GROUP_HEDGING,
        n0.symbol AS SYMBOL
    FROM (
        SELECT
            position_id, login, action, symbol, volume, time_create,
            ROW_NUMBER() OVER (
                PARTITION BY login, action
                ORDER BY time_create, position_id
            ) AS rn
        FROM '.$dbmetasrv.'.`mt5_positions` AS v_mt5_positions
        WHERE action IN (0,1)
        AND DATE(FROM_UNIXTIME(time_create)) >= DATE(FROM_UNIXTIME('.$startdate.')) 
        AND DATE(FROM_UNIXTIME(time_create)) < DATE_ADD(DATE(FROM_UNIXTIME('.$enddate.')), INTERVAL 2 DAY)
    ) AS n0
    JOIN (
        SELECT
            position_id, login, action, symbol, volume, time_create,
            ROW_NUMBER() OVER (
                PARTITION BY login, action
                ORDER BY time_create, position_id
            ) AS rn
        FROM '.$dbmetasrv.'.`mt5_positions` AS v_mt5_positions
        WHERE action IN (0,1)
        AND DATE(FROM_UNIXTIME(time_create)) >= DATE(FROM_UNIXTIME('.$startdate.')) 
        AND DATE(FROM_UNIXTIME(time_create)) < DATE_ADD(DATE(FROM_UNIXTIME('.$enddate.')), INTERVAL 2 DAY)
    ) AS n1
    ON n0.login  = n1.login
    AND n0.action = 0
    AND n1.action = 1
    AND n0.rn     = n1.rn
    AND n0.symbol = n1.symbol
    JOIN '.$dbmetasrv.'.mt5_users AS v_mt5_users ON(v_mt5_users.login = n0.login)
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = v_mt5_users.login)
    JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR)
    ORDER BY n0.login, n0.rn
');

echo $dt->generate()->toJson();