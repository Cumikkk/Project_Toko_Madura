<?php
use App\Models\Helper;
$accountInput = isset($_GET['account']) ? $db->real_escape_string($_GET['account']) : "";
$startDateInput = isset($_GET['start_date']) ? trim($_GET['start_date']) : "";
$endDateInput = isset($_GET['end_date']) ? trim($_GET['end_date']) : "";


$startDateSql = '';
$endDateSql = '';
if ($startDateInput !== '') {
    $startDateSql = $db->real_escape_string($startDateInput) . ' 00:00:00';
}

if ($endDateInput !== '') {
    $endDateSql = $db->real_escape_string($endDateInput) . ' 23:59:59';
}

$dateFilterSql = '';
if ($startDateSql !== '' && $endDateSql !== '') {
    $dateFilterSql = " AND rh.H_EXECUTE_AT BETWEEN '{$startDateSql}' AND '{$endDateSql}' ";
} elseif ($startDateSql !== '') {
    $dateFilterSql = " AND rh.H_EXECUTE_AT >= '{$startDateSql}' ";
} elseif ($endDateSql !== '') {
    $dateFilterSql = " AND rh.H_EXECUTE_AT <= '{$endDateSql}' ";
}

$dt->query("
    SELECT
        MD5(MD5(rh.H_CODE)) as `id`,
        MD5(MD5(rh.H_LOGIN)) as `id_login`,
        rh.H_EXECUTE_AT as executed_date,
        rh.H_CODE as code,
        dpwd.total_share as amount,
        SUM(rh.H_AMOUNT) as volume,
        dpwd.currency as currency
    FROM tb_rebate_history rh
    LEFT JOIN (
        SELECT 
            td.DPWD_CODE,
            td.DPWD_RACC,
            td.DPWD_CURR_TO as currency,
            SUM(td.DPWD_AMOUNT) as total_share
        FROM tb_dpwd as td
        WHERE td.DPWD_TYPE = 4
        GROUP BY td.DPWD_CODE, td.DPWD_RACC
    ) as dpwd ON (dpwd.DPWD_CODE = rh.H_CODE AND dpwd.DPWD_RACC = rh.H_LOGIN)
    WHERE MD5(MD5(rh.H_LOGIN)) = '{$accountInput}'
    {$dateFilterSql}
    GROUP BY rh.H_CODE, rh.H_LOGIN, id, id_login
");

$dt->hide('id');
$dt->hide('id_login');
$dt->hide('currency');
$dt->edit('code', function($col) {
    return '
        <a href="/account/sales_conditions/summary_rebate?code='.$col['id'].'&login='.$col['id_login'].'" target="_blank">
            <p class="mb-0">'.$col['code'].'</p>
        </a>
    ';
});
$dt->edit('amount', function($data) {
    return Helper::currencyToSymbol($data['currency']) . ' ' . Helper::formatCurrency($data['amount']);
});

$dt->edit('volume', function($data) {
    return $data['volume'] . ' lot';
});

echo $dt->generate()->toJson();
