<?php
use App\Models\Helper;
$data = Helper::getSafeInput($_GET);
if(!empty($data["dte"])){
    $DSDN       = explode(",", $data["dte"]);
    $date_start = $DSDN[0] ?? 0;
    $date_end   = $DSDN[1] ?? 0;
    $dte        = "AND DATE(tit.IT_DATETIME) BETWEEN DATE('$date_start') AND DATE('$date_end')";
}else{ $dte = ''; }

$dt->query("
    SELECT
        IT_DATETIME,
        IT_FROM,
        IT_COMMENT_FROM,
        IT_TICKET_FROM,
        IT_TO,
        IT_COMMENT_TO,
        IT_TICKET_TO,
        IT_AMOUNT,
        IT_CODE
    FROM tb_internal_transfer tit
    JOIN tb_racc trFrom ON (trFrom.ACC_LOGIN = tit.IT_FROM AND trFrom.ACC_DERE = 1 AND trFrom.ACC_MBR = ".$user['MBR_ID'].")
    JOIN tb_racc trTo ON (trTo.ACC_LOGIN = tit.IT_FROM AND trTo.ACC_DERE = 1 AND trTo.ACC_MBR = ".$user['MBR_ID'].")
    $dte
");

$dt->hide("IT_COMMENT_FROM");
$dt->hide("IT_TICKET_FROM");
$dt->edit("IT_FROM", function($col) {
    return '
        <p class="fw-bold">'.$col['IT_FROM'].'</p>
        <p class="mb-0">#'.$col['IT_TICKET_FROM'].'</p>
        <p class="mb-0">'.$col['IT_COMMENT_FROM'].'</p>
    ';
});

$dt->hide("IT_COMMENT_TO");
$dt->hide("IT_TICKET_TO");
$dt->edit("IT_TO", function($col) {
    return '
        <p class="fw-bold">'.$col['IT_TO'].'</p>
        <p class="mb-0">#'.$col['IT_TICKET_TO'].'</p>
        <p class="mb-0">'.$col['IT_COMMENT_TO'].'</p>
    ';
});

$dt->edit("IT_AMOUNT", fn($col): string => '$'.App\Models\Helper::formatCurrency($col['IT_AMOUNT']));
$dt->edit("IT_CODE", fn($col): string => "IT-".$col['IT_CODE']);

echo $dt->generate()->toJson();