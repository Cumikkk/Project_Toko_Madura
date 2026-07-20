<?php
use App\Models\Helper;
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$data = Helper::getSafeInput($_GET);

$enddate   = isset($data['enddate']) ? (int)$data['enddate'] : time();
$startdate = isset($data['startdate']) ? (int)$data['startdate'] : strtotime('-7 days', $enddate);

$dt->query("
    SELECT
        v_mt5_deals.login AS LOGIN,
        tb_member.MBR_NAME AS NAME,
        v_mt5_deals.profit AS AMOUNT
    FROM {$dbmetasrv}.mt5_deals v_mt5_deals
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = v_mt5_deals.login)
    JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR)
    WHERE v_mt5_deals.action = 2
    AND v_mt5_deals.profit < 0
    AND v_mt5_deals.time >= ".$startdate."
    AND v_mt5_deals.time < (".$enddate + (2 * 86400).")
    ORDER BY AMOUNT DESC
    LIMIT 10
");

echo $dt->generate()->toJson();