<?php
use App\Models\Helper;
use Config\Core\SystemInfo;
$data   = Helper::getSafeInput($_GET);

$enddate   = isset($data['enddate']) ? (int)$data['enddate'] : time();
$startdate = isset($data['startdate']) ? (int)$data['startdate'] : strtotime('-7 days', $enddate);
$dbmetasrv = SystemInfo::app('DB_METALIVE');

$trades = 0;
$profit = 0;
$sqlGet = $db->query("
SELECT
	IFNULL(COUNT(*), 0) AS COUNT_TRADES,
	IFNULL(SUM(sum_profit), 0) AS SUM_PROFIT
FROM (
	SELECT
	    v_mt5_deals.login,
	    v_mt5_users.`group`,
	    v_mt5_deals.position_id,
	    SUM(IF(v_mt5_deals.`entry` = 0, v_mt5_deals.volume/1000, 0)) AS total_in,
	    SUM(IF(v_mt5_deals.`entry` = 1, v_mt5_deals.volume/1000, 0)) AS total_out,
	    FROM_UNIXTIME(MIN(v_mt5_deals.`time`)) AS open_time,
	    FROM_UNIXTIME(MAX(v_mt5_deals.`time`)) AS close_time,
	    TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(MIN(v_mt5_deals.`time`)), FROM_UNIXTIME(MAX(v_mt5_deals.`time`))) AS difference,
	    SUM(IF(v_mt5_deals.`entry` = 1, v_mt5_deals.volume, 0)) AS sum_volume,
	    ROUND(IF(v_mt5_deals.`entry` = 0, v_mt5_deals.price, 0), v_mt5_deals.digits) AS open_price,
	    ROUND(AVG(CASE WHEN v_mt5_deals.`entry` = 1 THEN v_mt5_deals.price ELSE NULL END), v_mt5_deals.digits) AS close_price,
	    ROUND(SUM(v_mt5_deals.profit), v_mt5_deals.digits_currency) AS sum_profit
	FROM {$dbmetasrv}.mt5_deals AS v_mt5_deals
	JOIN {$dbmetasrv}.mt5_users AS v_mt5_users ON(v_mt5_users.login = v_mt5_deals.login)
	JOIN tb_racc ON(tb_racc.ACC_LOGIN = v_mt5_deals.login)
	WHERE v_mt5_deals.`action` IN (0, 1)
    AND v_mt5_deals.time >= ".$startdate."
    AND v_mt5_deals.time < (".$enddate + (2 * 86400).")
	GROUP BY v_mt5_deals.login, v_mt5_deals.position_id
	HAVING total_in = total_out
	AND difference <= 30
) tb1
");

if($sqlGet) {
    $data = $sqlGet->fetch_assoc();
    $trades = (int)$data['COUNT_TRADES'];
    $profit = (float)$data['SUM_PROFIT'];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => [
        'trades' => number_format($trades, 0),
        'profit' => number_format($profit, 2)
    ]
]);