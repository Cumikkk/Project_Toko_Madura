<?php
$dt->query("
    SELECT 
        tbi.BECOME_DATETIME,
        tm.MBR_NAME,
        tm.MBR_EMAIL,
        tm.MBR_CODE,
        tb_sales_division.SLSDIVISION_NAME,
        tb_sales_structure.SLSSTRC_NAME,
        tbi.BECOME_STS,
        tbi.ID_BECOME
    FROM tb_become_ib tbi
    JOIN tb_member tm ON (tm.MBR_ID = tbi.BECOME_MBR)
    LEFT JOIN tb_sales_structure ON (tb_sales_structure.ID_SLSSTRC = tm.MBR_TYPE)
    LEFT JOIN tb_sales_division ON (tb_sales_division.ID_SLSDIVISION = tb_sales_structure.SLSSTRC_DIV)
    WHERE BECOME_STS != 0
    AND tm.MBR_STS != 1
");

$dt->hide("MBR_CODE");

$dt->edit("MBR_EMAIL", fn($col): string => '<a target="_blank" href="/member/user/update/'.$col['MBR_CODE'].'/refferal">'.$col['MBR_EMAIL'].'</a>');
$dt->edit("BECOME_STS", fn($col): string => App\Models\Ib::$status[ $col['BECOME_STS'] ]['html']);

echo $dt->generate()->toJson();