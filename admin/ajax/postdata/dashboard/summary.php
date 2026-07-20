<?php
use Config\Core\SystemInfo;
$db->query("SET time_zone = '+07:00'");

$user_regester = 0;
$user_actived = 0;
$it_count = 0;
$it_total = 0.0;
$dp_idr = 0.0;
$dp_usd = 0.0;
$wd_idr = 0.0;
$wd_usd = 0.0;

$dbmetasrv = SystemInfo::app('DB_METALIVE');

$sqlSumaryCard = $db->query("
    SELECT
        COUNT(*) AS user_regester,
        SUM(IF(tb_member.MBR_STS = -1, 1, 0)) AS user_actived
    FROM tb_member
");
if ($sqlSumaryCard) {
    $rowSumaryCard = $sqlSumaryCard->fetch_assoc();
    if ($rowSumaryCard) {
        $user_regester = (int)$rowSumaryCard['user_regester'];
        $user_actived = (int)$rowSumaryCard['user_actived'];
    }
}

$sqlSumaryCard = $db->query("
    SELECT
        IFNULL(SUM(IF(tb_racctype.RTYPE_ISFLOATING = 0, tb_dpwd.DPWD_AMOUNT_SOURCE, 0)), 0) AS AMOUNT_DP_IDR,
        IFNULL(SUM(IF(tb_racctype.RTYPE_ISFLOATING = 1, tb_dpwd.DPWD_AMOUNT, 0)), 0) AS AMOUNT_DP_USD
    FROM tb_dpwd
    JOIN tb_racc ON(tb_racc.ID_ACC = tb_dpwd.DPWD_RACC)
    JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
    JOIN {$dbmetasrv}.MT4_USERS v_mt5_users ON(v_mt5_users.LOGIN = tb_racc.ACC_LOGIN)
    JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR AND tb_member.MBR_ID = tb_dpwd.DPWD_MBR)
    WHERE tb_dpwd.DPWD_STS = -1
    AND tb_dpwd.DPWD_TYPE = 1
");
if ($sqlSumaryCard) {
    $rowSumaryCard = $sqlSumaryCard->fetch_assoc();
    if ($rowSumaryCard) {
        $dp_idr = (float)$rowSumaryCard['AMOUNT_DP_IDR'];
        $dp_usd = (float)$rowSumaryCard['AMOUNT_DP_USD'];
    }
}

$sqlSumaryCard = $db->query("
    SELECT
        IFNULL(SUM(IF(tb_racctype.RTYPE_ISFLOATING = 0, tb_dpwd.DPWD_AMOUNT, 0)), 0) AS AMOUNT_WD_IDR,
        IFNULL(SUM(IF(tb_racctype.RTYPE_ISFLOATING = 1, tb_dpwd.DPWD_AMOUNT_SOURCE, 0)), 0) AS AMOUNT_WD_USD
    FROM tb_dpwd
    JOIN tb_racc ON(tb_racc.ID_ACC = tb_dpwd.DPWD_RACC)
    JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
    JOIN {$dbmetasrv}.MT4_USERS v_mt5_users ON(v_mt5_users.LOGIN = tb_racc.ACC_LOGIN)
    JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR AND tb_member.MBR_ID = tb_dpwd.DPWD_MBR)
    WHERE tb_dpwd.DPWD_STS = -1
    AND tb_dpwd.DPWD_TYPE = 2
");
if ($sqlSumaryCard) {
    $rowSumaryCard = $sqlSumaryCard->fetch_assoc();
    if ($rowSumaryCard) {
        $wd_idr = (float)$rowSumaryCard['AMOUNT_WD_IDR'];
        $wd_usd = (float)$rowSumaryCard['AMOUNT_WD_USD'];
    }
}

$sqlSumaryCard = $db->query("
    SELECT        
        COUNT(*) AS TOTAL_COUNT,
        IFNULL(SUM(IT_AMOUNT), 0) AS TOTAL_IT
    FROM tb_internal_transfer
    JOIN tb_racc from_tb_racc ON (from_tb_racc.ACC_LOGIN = tb_internal_transfer.IT_FROM)
    JOIN tb_member from_tb_member ON (from_tb_member.MBR_ID = from_tb_racc.ACC_MBR)
    JOIN {$dbmetasrv}.MT4_USERS from_mt4_users ON (from_mt4_users.LOGIN = tb_internal_transfer.IT_FROM)
    JOIN tb_racc to_tb_racc ON (to_tb_racc.ACC_LOGIN = tb_internal_transfer.IT_TO)
    JOIN tb_member to_tb_member ON (to_tb_member.MBR_ID = to_tb_racc.ACC_MBR)
    JOIN {$dbmetasrv}.MT4_USERS to_mt4_users ON (to_mt4_users.LOGIN = tb_internal_transfer.IT_TO)
");
if ($sqlSumaryCard) {
    $rowSumaryCard = $sqlSumaryCard->fetch_assoc();
    if ($rowSumaryCard) {
        $it_count = (float)$rowSumaryCard['TOTAL_COUNT'];
        $it_total = (float)$rowSumaryCard['TOTAL_IT'];
    }
}

$labelsIdrDpWdIt = [];
$DpSeriesIdrDpWdIt = [];
$WdSeriesIdrDpWdIt = [];

$sqlChartIdrDpWdIt = $db->query("
    SELECT
        DATE_FORMAT(dates.d, '%d/%m') AS label_ddmm,
        COALESCE(m.cnt, 0)            AS member_count,
        COALESCE(a.cnt, 0)            AS account_count
    FROM (
        SELECT (CURDATE() - INTERVAL 29 DAY) + INTERVAL n DAY AS d
        FROM (
            SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
            UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
            UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
            UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
            UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
        ) AS nums
    ) AS dates
    LEFT JOIN (
        SELECT DATE(tb_dpwd.DPWD_DATETIME) AS dt, IFNULL(SUM(tb_dpwd.DPWD_AMOUNT_SOURCE), 0) AS cnt
        FROM tb_dpwd
        JOIN tb_racc ON(tb_racc.ID_ACC = tb_dpwd.DPWD_RACC)
        JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        JOIN {$dbmetasrv}.MT4_USERS v_mt4_users ON(v_mt4_users.LOGIN = tb_racc.ACC_LOGIN)
        JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR AND tb_member.MBR_ID = tb_dpwd.DPWD_MBR)
        WHERE tb_dpwd.DPWD_STS = -1
        AND tb_dpwd.DPWD_TYPE = 1
        AND tb_racctype.RTYPE_ISFLOATING = 0
        GROUP BY tb_dpwd.DPWD_DATETIME
    ) AS m ON(m.dt = dates.d)
    LEFT JOIN (
        SELECT DATE(tb_dpwd.DPWD_DATETIME) AS dt, IFNULL(SUM(tb_dpwd.DPWD_AMOUNT_SOURCE), 0) AS cnt
        FROM tb_dpwd
        JOIN tb_racc ON(tb_racc.ID_ACC = tb_dpwd.DPWD_RACC)
        JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        JOIN {$dbmetasrv}.MT4_USERS v_mt4_users ON(v_mt4_users.LOGIN = tb_racc.ACC_LOGIN)
        JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR AND tb_member.MBR_ID = tb_dpwd.DPWD_MBR)
        WHERE tb_dpwd.DPWD_STS = -1
        AND tb_dpwd.DPWD_TYPE = 2
        AND tb_racctype.RTYPE_ISFLOATING = 0
        GROUP BY tb_dpwd.DPWD_DATETIME
    ) AS a ON(a.dt = dates.d)
    WHERE dates.d <= CURDATE()
    ORDER BY dates.d ASC
");
if ($sqlChartIdrDpWdIt) {
    while ($rowChartIdrDpWdIt = $sqlChartIdrDpWdIt->fetch_assoc()) {
        $labelsIdrDpWdIt[] = $rowChartIdrDpWdIt['label_ddmm'];
        $DpSeriesIdrDpWdIt[] = (float)$rowChartIdrDpWdIt['member_count'];
        $WdSeriesIdrDpWdIt[] = (float)$rowChartIdrDpWdIt['account_count'];
    }
}

$labelsUsdDpWdIt = [];
$DpSeriesUsdDpWdIt = [];
$WdSeriesUsdDpWdIt = [];
$ItSeriesUsdDpWdIt = [];

$sqlChartUsdDpWdIt = $db->query("
    SELECT
        DATE_FORMAT(dates.d, '%d/%m') AS label_ddmm,
        COALESCE(m.cnt, 0)            AS member_count,
        COALESCE(a.cnt, 0)            AS account_count,
        COALESCE(it.cnt, 0)           AS it_count
    FROM (
        SELECT (CURDATE() - INTERVAL 29 DAY) + INTERVAL n DAY AS d
        FROM (
            SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
            UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
            UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
            UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
            UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
        ) AS nums
    ) AS dates
    LEFT JOIN (
        SELECT DATE(tb_dpwd.DPWD_DATETIME) AS dt, IFNULL(SUM(tb_dpwd.DPWD_AMOUNT_SOURCE), 0) AS cnt
        FROM tb_dpwd
        JOIN tb_racc ON(tb_racc.ID_ACC = tb_dpwd.DPWD_RACC)
        JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        JOIN {$dbmetasrv}.MT4_USERS v_mt4_users ON(v_mt4_users.LOGIN = tb_racc.ACC_LOGIN)
        JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR AND tb_member.MBR_ID = tb_dpwd.DPWD_MBR)
        WHERE tb_dpwd.DPWD_STS = -1
        AND tb_dpwd.DPWD_TYPE = 1
        AND tb_racctype.RTYPE_ISFLOATING = 1
        GROUP BY tb_dpwd.DPWD_DATETIME
    ) AS m ON(m.dt = dates.d)
    LEFT JOIN (
        SELECT DATE(tb_dpwd.DPWD_DATETIME) AS dt, IFNULL(SUM(tb_dpwd.DPWD_AMOUNT_SOURCE), 0) AS cnt
        FROM tb_dpwd
        JOIN tb_racc ON(tb_racc.ID_ACC = tb_dpwd.DPWD_RACC)
        JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        JOIN {$dbmetasrv}.MT4_USERS v_mt4_users ON(v_mt4_users.LOGIN = tb_racc.ACC_LOGIN)
        JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR AND tb_member.MBR_ID = tb_dpwd.DPWD_MBR)
        WHERE tb_dpwd.DPWD_STS = -1
        AND tb_dpwd.DPWD_TYPE = 2
        AND tb_racctype.RTYPE_ISFLOATING = 1
        GROUP BY tb_dpwd.DPWD_DATETIME
    ) AS a ON(a.dt = dates.d)
    LEFT JOIN (
        SELECT DATE(tb_internal_transfer.IT_DATETIME) AS dt, IFNULL(SUM(IT_AMOUNT), 0) AS cnt
        FROM tb_internal_transfer
        JOIN tb_racc from_tb_racc ON (from_tb_racc.ACC_LOGIN = tb_internal_transfer.IT_FROM)
        JOIN tb_member from_tb_member ON (from_tb_member.MBR_ID = from_tb_racc.ACC_MBR)
        JOIN {$dbmetasrv}.MT4_USERS from_mt4_users ON (from_mt4_users.LOGIN = tb_internal_transfer.IT_FROM)
        JOIN tb_racc to_tb_racc ON (to_tb_racc.ACC_LOGIN = tb_internal_transfer.IT_TO)
        JOIN tb_member to_tb_member ON (to_tb_member.MBR_ID = to_tb_racc.ACC_MBR)
        JOIN {$dbmetasrv}.MT4_USERS to_mt4_users ON (to_mt4_users.LOGIN = tb_internal_transfer.IT_TO)
        GROUP BY DATE(tb_internal_transfer.IT_DATETIME)
    ) AS it ON(it.dt = dates.d)
    WHERE dates.d <= CURDATE()
    ORDER BY dates.d ASC
");
if ($sqlChartUsdDpWdIt) {
    while ($rowChartUsdDpWdIt = $sqlChartUsdDpWdIt->fetch_assoc()) {
        $labelsUsdDpWdIt[] = $rowChartUsdDpWdIt['label_ddmm'];
        $DpSeriesUsdDpWdIt[] = (float)$rowChartUsdDpWdIt['member_count'];
        $WdSeriesUsdDpWdIt[] = (float)$rowChartUsdDpWdIt['account_count'];
        $ItSeriesUsdDpWdIt[] = (float)$rowChartUsdDpWdIt['it_count'];
    }
}

JsonResponse([
    'success' => true,
    'message' => 'Successful',
    'data' => [
        'user_regester' => number_format($user_regester, 0),
        'user_actived'  => number_format($user_actived, 0),
        'it_count'      => number_format($dp_idr-$wd_idr, 0),
        'it_total'      => number_format($dp_usd-$wd_usd, 2),
        'dp_idr'        => number_format($dp_idr, 0),
        'dp_usd'        => number_format($dp_usd, 2),
        'wd_idr'        => number_format($wd_idr, 0),
        'wd_usd'        => number_format($wd_usd, 2)
    ],
    'chartIdrDpWdIt' => [
        'labels' => $labelsIdrDpWdIt,
        'dp_series' => $DpSeriesIdrDpWdIt,
        'wd_series' => $WdSeriesIdrDpWdIt
    ],
    'chartUsdDpWdIt' => [
        'labels' => $labelsUsdDpWdIt,
        'dp_series' => $DpSeriesUsdDpWdIt,
        'wd_series' => $WdSeriesUsdDpWdIt,
        'it_series' => $ItSeriesUsdDpWdIt
    ]
]);
