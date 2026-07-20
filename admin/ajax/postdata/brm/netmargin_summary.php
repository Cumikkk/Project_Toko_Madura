<?php
use App\Models\Helper;
use Config\Core\SystemInfo;
$data   = Helper::getSafeInput($_GET);

$enddate   = isset($data['enddate']) ? (int)$data['enddate'] : time();
$startdate = isset($data['startdate']) ? (int)$data['startdate'] : strtotime('-7 days', $enddate);

$deposit = 0;
$withdrawal = 0;
$net = 0;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$sqlGet = $db->query("
    SELECT
        IFNULL(SUM(IF(v_mt5_deals.profit > 0, v_mt5_deals.profit, 0)), 0) AS deposit,
        IFNULL(SUM(IF(v_mt5_deals.profit < 0, v_mt5_deals.profit, 0)), 0) AS withdrawal,
        IFNULL(SUM(v_mt5_deals.profit), 0) AS net
    FROM {$dbmetasrv}.mt5_deals v_mt5_deals
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = v_mt5_deals.login)
    WHERE v_mt5_deals.action = 2
    AND v_mt5_deals.time >= ".$startdate."
    AND v_mt5_deals.time < (".$enddate + (2 * 86400).")
");

if($sqlGet) {
    $data = $sqlGet->fetch_assoc();
    $deposit = (float)$data['deposit'];
    $withdrawal = (float)$data['withdrawal'];
    $net = (float)$data['net'];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => [
        'deposit' => number_format($deposit, 2),
        'withdrawal' => number_format($withdrawal, 2),
        'net' => number_format($net, 2)
    ]
]);