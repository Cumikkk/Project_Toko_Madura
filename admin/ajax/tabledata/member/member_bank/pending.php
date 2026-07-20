<?php

use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
use App\Models\MemberBank;
$dt->query("
    SELECT
        IFNULL(tmb.MBANK_TIMESTAMP, tmb.MBANK_DATETIME) as MBANK_DATETIME,
        tm.MBR_NAME,
        tm.MBR_EMAIL,
        tmb.MBANK_NAME,
        tmb.MBANK_HOLDER,
        tmb.MBANK_ACCOUNT,
        tmb.MBANK_IMG,
        tmb.ID_MBANK
    FROM tb_member_bank tmb
    JOIN tb_member tm ON (tm.MBR_ID = tmb.MBANK_MBR)
    WHERE MBANK_STS = ".MemberBank::$statusPending."
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

$dt->edit("ID_MBANK", function($col) {
    return '
        <div class="action d-flex gap-2 justify-content-center align-items-center" data-id="'.md5(md5($col['ID_MBANK'])).'">
           
        </div>
    ';
});

echo $dt->generate()->toJson();