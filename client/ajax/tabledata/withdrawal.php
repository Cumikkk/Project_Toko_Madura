<?php
use App\Models\Helper;
$data = Helper::getSafeInput($_GET);
if(!empty($data["dte"])){
    $DSDN       = explode(",", $data["dte"]);
    $date_start = $DSDN[0] ?? 0;
    $date_end   = $DSDN[1] ?? 0;
    $dte        = "AND DATE(td.DPWD_DATETIME) BETWEEN DATE('$date_start') AND DATE('$date_end')";
}else{ $dte = ''; }
$dt->query("
    SELECT 
        td.DPWD_DATETIME,
        tr.ACC_LOGIN,
        td.DPWD_AMOUNT_SOURCE,
        td.DPWD_AMOUNT,
        td.DPWD_CURR_FROM,
        td.DPWD_CURR_TO,
        td.DPWD_RATE,
        td.DPWD_STS,
        td.DPWD_NOTE1
    FROM tb_dpwd td
    JOIN tb_racc tr ON (tr.ID_ACC = td.DPWD_RACC) 
    WHERE td.DPWD_TYPE = 2
    AND td.DPWD_MBR = ".$user['MBR_ID']."
    $dte
");

$dt->hide("DPWD_CURR_FROM");
$dt->hide("DPWD_CURR_TO");
$dt->edit("DPWD_AMOUNT_SOURCE", fn($col): string => App\Models\Helper::formatCurrency($col['DPWD_AMOUNT_SOURCE']) . " " . $col['DPWD_CURR_FROM']);
$dt->edit("DPWD_AMOUNT", fn($col): string => App\Models\Helper::formatCurrency($col['DPWD_AMOUNT']) . " " . $col['DPWD_CURR_TO']);
$dt->edit("DPWD_STS", fn($col): string => App\Models\Dpwd::$status[ $col['DPWD_STS'] ]['html']);

echo $dt->generate()->toJson();