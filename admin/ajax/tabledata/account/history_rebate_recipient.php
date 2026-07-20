<?php
use App\Models\Helper;
$codeInput = isset($_GET['code']) ? $db->real_escape_string($_GET['code']) : "";
$loginInput = isset($_GET['login']) ? $db->real_escape_string($_GET['login']) : "";

$dt->query(" 
    SELECT
        IFNULL(tm.MBR_NAME, '-') as `name`,
        IFNULL(tss.SLSSTRC_NAME, 'Trader') as `sales_type`,
        JSON_UNQUOTE(JSON_EXTRACT(td.DPWD_METADATA, '$.symbol')) as `symbol`,
        JSON_UNQUOTE(JSON_EXTRACT(td.DPWD_METADATA, '$.group')) as `kategori`,
        IFNULL(td.DPWD_CURR_TO, '-') as `currency`,
        JSON_UNQUOTE(JSON_EXTRACT(td.DPWD_METADATA, '$.commission')) as `commission`,
        JSON_UNQUOTE(JSON_EXTRACT(td.DPWD_METADATA, '$.volume')) as `volume`,
        IFNULL(td.DPWD_AMOUNT, 0) as `amount`
    FROM (
        SELECT
            DPWD_MBR,
            DPWD_AMOUNT,
            DPWD_CURR_TO,
            DPWD_METADATA,
            DPWD_NOTE,
            DPWD_CODE,
            DPWD_RACC
        FROM tb_dpwd
        WHERE DPWD_TYPE = 4
        AND MD5(MD5(DPWD_CODE)) = '{$codeInput}'
        AND MD5(MD5(DPWD_RACC)) = '{$loginInput}'
    ) as td
    LEFT JOIN tb_member tm ON tm.MBR_ID = td.DPWD_MBR
    LEFT JOIN tb_sales_structure tss ON tss.ID_SLSSTRC = tm.MBR_TYPE
    ORDER BY tm.MBR_NAME ASC
");

// SELECT
//     IFNULL(tm.MBR_NAME, '-') as `name`,
//     IFNULL(tss.SLSSTRC_NAME, 'Trader') as `sales_type`,
//     IFNULL(td.DPWD_AMOUNT, 0) as `amount`,
//     IFNULL(td.DPWD_CURR_TO, '-') as `currency`,
//     IFNULL(rh.H_VOLUME, 0) as `volume`,
//     JSON_UNQUOTE(JSON_EXTRACT(td.DPWD_METADATA, '$.group')) as `kategori`,
//     IFNULL((
//         SELECT md.symbol
//         FROM mt5_deals md
//         WHERE md.deal_id = rh.H_TICKET
//         LIMIT 1
//     ), '-') as `symbol`,
//     IFNULL(td.DPWD_NOTE, '-') as `note`
// FROM (
//     SELECT
//         DPWD_MBR,
//         DPWD_AMOUNT,
//         DPWD_CURR_TO,
//         DPWD_METADATA,
//         DPWD_NOTE,
//         DPWD_CODE,
//         DPWD_RACC
//     FROM tb_dpwd
//     WHERE DPWD_TYPE = 4
//       AND MD5(MD5(DPWD_CODE)) = 'c5adaf4a7037a0bec4c4a7e2a2038ed6'
//       AND MD5(MD5(DPWD_RACC)) = '46a8933329726a0d4bdd14563338e175'
// ) as td
// LEFT JOIN tb_member tm ON tm.MBR_ID = td.DPWD_MBR
// LEFT JOIN tb_sales_structure tss ON tss.ID_SLSSTRC = tm.MBR_TYPE
// LEFT JOIN (
//     SELECT 
//         MAX(rh.H_TICKET) as H_TICKET,
//         SUM(rh.H_AMOUNT) as H_VOLUME,
//         rh.H_CODE,
//         rh.H_LOGIN
//     FROM tb_rebate_history rh
//     INNER JOIN (
//         SELECT DISTINCT DPWD_CODE, DPWD_RACC
//         FROM tb_dpwd
//         WHERE DPWD_TYPE = 4
//           AND MD5(MD5(DPWD_CODE)) = 'c5adaf4a7037a0bec4c4a7e2a2038ed6'
//           AND MD5(MD5(DPWD_RACC)) = '46a8933329726a0d4bdd14563338e175'
//     ) as tdf ON tdf.DPWD_CODE = rh.H_CODE AND tdf.DPWD_RACC = rh.H_LOGIN
//     GROUP BY rh.H_CODE, rh.H_LOGIN
// ) as rh ON (td.DPWD_CODE = rh.H_CODE AND td.DPWD_RACC = rh.H_LOGIN)
// ORDER BY tm.MBR_NAME ASC

$dt->hide('currency');

$dt->edit('kategori', function($data) {
    return ucfirst($data['kategori']);
});

$dt->edit('volume', function($data) {
    return round($data['volume'], 2) . ' lot';
});

$dt->edit('amount', function($data) {
    return Helper::currencyToSymbol($data['currency']) . ' ' . Helper::formatCurrency($data['amount']);
});

echo $dt->generate()->toJson();
