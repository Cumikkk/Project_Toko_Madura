<?php

use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
use App\Models\MemberBank;
$dt->query("
    SELECT
        tmb.MBANK_DATETIME,
        IFNULL(tmb.MBANK_TIMESTAMP, '-') as LAST_UPDATE,
        tm.MBR_NAME,
        tm.MBR_EMAIL,
        tmb.MBANK_NAME,
        tmb.MBANK_HOLDER,
        tmb.MBANK_ACCOUNT,
        tmb.MBANK_IMG
    FROM tb_member_bank tmb
    JOIN tb_member tm ON (tm.MBR_ID = tmb.MBANK_MBR)
    WHERE MBANK_STS = ".MemberBank::$statusAccepted."
    AND tm.MBR_STS != 1
");

$dt->hide("MBR_NAME");
$dt->edit("MBR_EMAIL", function($col) {
    return '
        <p class="mb-0">'.$col['MBR_NAME'].'</p>
        <p class="mb-0">'.$col['MBR_EMAIL'].'</p>
    ';
});

$dt->hide("MBANK_NAME");
$dt->hide("MBANK_HOLDER");
$dt->edit("MBANK_ACCOUNT", function($col) {
    return '
        <p class="mb-0">'.$col['MBANK_NAME'].'</p>
        <p class="mb-0">'.$col['MBANK_HOLDER'].' / '.$col['MBANK_ACCOUNT'].'</p>
    ';
});

$dt->edit("MBANK_IMG", fn($col): string => empty($col['MBANK_IMG']) ? '' : '<a target="_blank" href="'.FileUploadFactory::aws()->awsFile($col['MBANK_IMG']).'"><i>Lihat</i></a>');

echo $dt->generate()->toJson();