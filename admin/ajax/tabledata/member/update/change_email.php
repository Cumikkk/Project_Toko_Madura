<?php
use App\Factory\FileUploadFactory;
use App\Models\Helper;
use App\Models\FileUpload;
$userCode = Helper::form_input($_GET['code'] ?? "-");
$dt->query('
    SELECT
        tb_chmail_log.CHML_DATETIME,
        tb_chmail_log.CHML_PREV_MAIL,
        tb_chmail_log.CHML_NEXT_MAIL,
        tb_chmail_log.CHML_FILE
    FROM tb_chmail_log
    JOIN tb_member tm ON (tm.MBR_ID = tb_chmail_log.CHML_MBR)
    WHERE tm.MBR_CODE = "'.$userCode.'"
');

$dt->edit('CHML_DATETIME', function($data){
    return '
        <div class="text-center">
            '.$data["CHML_DATETIME"].'
        </div>
    ';
});

$dt->edit('CHML_FILE', function($data){
    if(!empty($data["CHML_FILE"])){
        return '
            <div class="text-center">
                <a target="_blank" href="'.FileUploadFactory::aws()->awsFile($data["CHML_FILE"]).'">Open</a>
            </div>
        ';
    }
});

echo $dt->generate()->toJson();