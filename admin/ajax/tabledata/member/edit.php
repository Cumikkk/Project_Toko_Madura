<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Helper;
    use App\Models\FileUpload;
    $SFD  = Helper::getSafeInput($_GET);
    $usrx = $SFD["usrx"];
    $dt->query('
        SELECT
            tb_chmail_log.CHML_DATETIME,
            tb_chmail_log.CHML_PREV_MAIL,
            tb_chmail_log.CHML_NEXT_MAIL,
            tb_chmail_log.CHML_FILE
        FROM tb_chmail_log
        WHERE MD5(MD5(tb_chmail_log.CHML_MBR)) = "'.$usrx.'"
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