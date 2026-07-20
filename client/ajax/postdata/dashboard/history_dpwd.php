<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function jres(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge([
        'success' => $ok,
        'message' => $msg,
    ], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$range = isset($_GET['range']) ? strtolower(trim($_GET['range'])) : '7d';
if (!in_array($range, ['7d','1m','1y'], true)) $range = '7d';

$daysMap = ['7d'=>7, '1m'=>30, '1y'=>365];
$days    = $daysMap[$range];

$todayMid = strtotime(date('Y-m-d 00:00:00'));
$datesYmd = [];
$catISO   = [];
for ($i = $days - 1; $i >= 0; $i--) {
    $ts   = strtotime("-{$i} day", $todayMid);
    $ymd  = date('Y-m-d', $ts);
    $iso  = date(DATE_ATOM, $ts);
    $datesYmd[] = $ymd;
    $catISO[]   = $iso;
}
$idxByDate = array_flip($datesYmd);

$DP_IDR = array_fill(0, $days, 0.0);
$DP_USD = array_fill(0, $days, 0.0);
$WD_IDR = array_fill(0, $days, 0.0);
$WD_USD = array_fill(0, $days, 0.0);

$from = date('Y-m-d 00:00:00', strtotime('-'.($days-1).' day', $todayMid));
$to   = date('Y-m-d 00:00:00', strtotime('+1 day', $todayMid));

try {
    $sql_get_dpwd = mysqli_query($db, "
        SELECT
            SUM(tb_dpwd.DPWD_AMOUNT_SOURCE) AS total,
            IF(tb_dpwd.DPWD_TYPE = 1 OR tb_dpwd.DPWD_TYPE = 3, 'DP', 'WD') AS `type`,
            tb_racc.ACC_LOGIN,
            tb_dpwd.DPWD_CURR_FROM AS currency,
            DATE(tb_dpwd.DPWD_DATETIME) as `d`
        FROM tb_dpwd
        JOIN tb_racc ON(tb_dpwd.DPWD_RACC = tb_racc.ID_ACC AND tb_dpwd.DPWD_MBR = tb_racc.ACC_MBR)
        JOIN tb_racctype ON(tb_racc.ACC_TYPE = tb_racctype.ID_RTYPE)
        WHERE MD5(MD5(tb_dpwd.DPWD_MBR)) = '{$userid}'
        AND DATE(tb_dpwd.DPWD_DATETIME) BETWEEN '".$from."' AND '".$to."'
        AND tb_racc.ACC_DERE = 1
        AND tb_racc.ACC_STS = -1
        AND (
            (tb_dpwd.DPWD_TYPE IN (1, 3) AND tb_dpwd.DPWD_STS = -1)
            OR 
            (tb_dpwd.DPWD_TYPE = 2 AND tb_dpwd.DPWD_STS != 1)
        )
        GROUP BY 
            DATE(tb_dpwd.DPWD_DATETIME),
            tb_dpwd.DPWD_CURR_FROM
    ");

    if($sql_get_dpwd && mysqli_num_rows($sql_get_dpwd) != 0) {
        while($row = mysqli_fetch_assoc($sql_get_dpwd)) {
            $dayKey   = $row['d'];
            $type     = strtoupper($row['type'] ?? '');
            $currency = strtoupper($row['currency'] ?? '');
            $total    = (float)$row['total'];

            if (!isset($idxByDate[$dayKey])) {
                continue;
            }
            $i = $idxByDate[$dayKey];

            if ($type === 'DP' && $currency === 'IDR') {
                $DP_IDR[$i] = $total;
            } elseif ($type === 'DP' && $currency === 'USD') {
                $DP_USD[$i] = $total;
            } elseif ($type === 'WD' && $currency === 'IDR') {
                $WD_IDR[$i] = $total;
            } elseif ($type === 'WD' && $currency === 'USD') {
                $WD_USD[$i] = $total;
            }
        }
    }
} catch (Throwable $e) {
    jres(false, 'Query failed '.$from, ['error' => $e->getMessage()]);
}

jres(true, 'Success', [
    'alert' => [
        'title' => 'Success',
        'text'  => 'Success',
        'icon'  => 'success',
    ],
    'data' => [
        'range'       => $range,
        'granularity' => 'day',
        'categories'  => $catISO,
        'chart' => [
            'DP_IDR' => $DP_IDR,
            'DP_USD' => $DP_USD,
            'WD_IDR' => $WD_IDR,
            'WD_USD' => $WD_USD,
        ],
    ],
]);