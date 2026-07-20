<?php

use App\Models\Helper;
use App\Models\SalesStructure;

$type = (int) Helper::form_input($_GET['currentSearch'] ?? -1);
$dt->query("
    SELECT
        tm.MBR_EMAIL,
        tm.MBR_NAME,
        tm.MBR_PHONE,
        tss.SLSSTRC_NAME,
        tm.MBR_CODE
    FROM tb_member tm
    JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = tm.MBR_TYPE)
    WHERE tm.MBR_ID != 1000000000
    AND tm.MBR_TYPE = {$type}
");

$dt->hide("MBR_CODE");
$dt->edit("MBR_EMAIL", fn($col): string => '<a target="_blank" href="/member/user/update/'.$col['MBR_CODE'].'/refferal">'.$col['MBR_EMAIL'].'</a>');

echo $dt->generate()->toJson();