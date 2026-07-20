<?php
use App\Models\Helper;
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$data = Helper::getSafeInput($_GET);

$filter_date = isset($data['filter_date']) ? $data['filter_date'] : date('Y-m-d');
$filter_month_start = date('Y-m-01', strtotime($filter_date));
$filter_month_end = date('Y-m-01', strtotime($filter_date . ' +1 month'));

if($filter_date = date('Y-m-d')) {
    $query_exeption = date('Y-m-d', strtotime('-1 day'));
} else {
    $query_addition = date('Y-m-d');
}

$dt->query("
    SELECT
        mt5users.login,
        tb_member.MBR_NAME,
        tb_member.MBR_EMAIL,
        tb_racctype.RTYPE_NAME,
        DATE(FROM_UNIXTIME(SUBSTRING_INDEX(
        GROUP_CONCAT(
            IF(mt5deals.`action` = 2 AND mt5deals.profit > 0, CONCAT(mt5deals.`time`, ':', mt5deals.`time`), NULL) 
            ORDER BY mt5deals.`time` DESC 
            SEPARATOR ','
        ), 
        ':', 
        -1
        ))) as DEPOSIT_NEWACCOUNT_DATE,
        tb_racctype.RTYPE_RATE,
        tb_racctype.RTYPE_KOMISI,
        SUBSTRING_INDEX(
        GROUP_CONCAT(
            IF(mt5deals.`action` = 2 AND mt5deals.profit > 0, CONCAT(mt5deals.`time`, ':', mt5deals.profit), NULL) 
            ORDER BY mt5deals.`time` DESC 
            SEPARATOR ','
        ), 
        ':', 
        -1
        ) as DEPOSIT_NEWACCOUNT,
        SUM(IF(mt5deals.profit > 0 AND mt5deals.action = 2 AND DATE(FROM_UNIXTIME(mt5deals.`time`)) = '".$filter_date."', mt5deals.profit, 0)) as TOTAL_DEPOSIT_TODAY,
        SUM(IF(mt5deals.profit > 0 AND mt5deals.action = 2 AND mt5deals.`time` >= UNIX_TIMESTAMP('".$filter_month_start."') AND mt5deals.`time` <  UNIX_TIMESTAMP('".$filter_month_end."'), mt5deals.profit, 0)) as TOTAL_DEPOSIT_MONTH,
        ABS(SUM(IF(mt5deals.profit < 0 AND mt5deals.action = 2 AND DATE(FROM_UNIXTIME(mt5deals.`time`)) = '".$filter_date."', mt5deals.profit, 0))) as TOTAL_WITHDRAWAL_TODAY,
        ABS(SUM(IF(mt5deals.profit < 0 AND mt5deals.action = 2 AND mt5deals.`time` >= UNIX_TIMESTAMP('".$filter_month_start."') AND mt5deals.`time` <  UNIX_TIMESTAMP('".$filter_month_end."'), mt5deals.profit, 0))) as TOTAL_WITHDRAWAL_MONTH
    FROM ".$dbmetasrv.".mt5_users mt5users
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = mt5users.login)
    JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR)
    JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
    LEFT JOIN ".$dbmetasrv.".mt5_deals mt5deals ON(mt5deals.login = mt5users.login)
    GROUP BY mt5users.login
");

echo $dt->generate()->toJson();
?>