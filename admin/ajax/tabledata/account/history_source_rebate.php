<?php
use App\Models\Helper;
$accountInput = isset($_GET['account']) ? $db->real_escape_string($_GET['account']) : "";

$dt->query(" 
    SELECT
        td.DPWD_DATETIME as `date`,
        td.DPWD_RACC as `login`,
        IFNULL(tm.MBR_NAME, '-') as `name`,
        IFNULL(tss.SLSSTRC_NAME, 'Trader') as `sales_type`,
        IFNULL(td.DPWD_CURR_TO, 0) as `currency`,
        JSON_UNQUOTE(JSON_EXTRACT(td.DPWD_METADATA, '$.commission')) as `commission`,
        JSON_UNQUOTE(JSON_EXTRACT(td.DPWD_METADATA, '$.volume')) as `volume`,
        IFNULL(td.DPWD_AMOUNT, 0) as `amount`
    FROM tb_dpwd td
    LEFT JOIN (
        SELECT 
            rh.H_AMOUNT,
            rh.H_CODE,
            rh.H_LOGIN
        FROM tb_rebate_history rh
        GROUP BY rh.H_CODE, rh.H_LOGIN
    ) as rh ON (td.DPWD_CODE = rh.H_CODE AND td.DPWD_RACC = rh.H_LOGIN)
    LEFT JOIN tb_racc racc ON racc.ACC_LOGIN = td.DPWD_RACC
    LEFT JOIN tb_member tm ON tm.MBR_ID = racc.ACC_MBR
    LEFT JOIN tb_sales_structure tss ON tss.ID_SLSSTRC = tm.MBR_TYPE
    WHERE td.DPWD_TYPE = 4
    AND MD5(MD5(td.DPWD_MBR)) = '{$accountInput}'
");
$dt->hide('currency');
$dt->edit('amount', function($data) {
    return Helper::currencyToSymbol($data['currency']) . ' ' . Helper::formatCurrency($data['amount']);
});

$dt->edit('volume', function($data) {
    return $data['volume'] . ' lot';
});
echo $dt->generate()->toJson();
