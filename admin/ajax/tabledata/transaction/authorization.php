<?php

    use App\Factory\FileUploadFactory;
    use App\Models\FileUpload;

    $dt->query('
        SELECT
            tb_dpwd.DPWD_DATETIME,
            tb_racc.ACC_FULLNAME,
            tb_member.MBR_EMAIL,
            tb_racc.ACC_LOGIN,
            IF(tb_racctype.RTYPE_ISFLOATING = 0, tb_dpwd.DPWD_AMOUNT_SOURCE, tb_dpwd.DPWD_AMOUNT) AS DPWD_AMOUNT,
            tb_dpwd.DPWD_PIC,				
            MD5(MD5(tb_dpwd.ID_DPWD)) AS ID_DPWD,
            JSON_OBJECT(
                "auth-login", tb_racc.ACC_LOGIN,
                "auth-name", tb_racc.ACC_FULLNAME,
                "auth-email", tb_member.MBR_EMAIL,
                "auth-amntl", CAST(CONCAT(tb_dpwd.DPWD_CURR_FROM, " ", FORMAT(tb_dpwd.DPWD_AMOUNT_SOURCE, 0)) AS CHAR),
                "auth-rate", CAST(IF(tb_racctype.RTYPE_ISFLOATING != 1, FORMAT(tb_racctype.RTYPE_RATE, 0), 0) AS CHAR),
                "auth-amnt", CAST(CONCAT(tb_dpwd.DPWD_CURR_TO, " ", FORMAT(tb_dpwd.DPWD_AMOUNT, 2)) AS CHAR),
                "auth-bksrc", tb_dpwd.DPWD_BANKSRC,
                "auth-bkdst", tb_dpwd.DPWD_BANK,
                "auth-dpx", CAST(MD5(MD5(tb_dpwd.ID_DPWD)) AS CHAR)
            ) AS JSNDT,
             tb_racctype.RTYPE_CURR_SYMBOL
        FROM tb_member
        JOIN tb_racc
        JOIN tb_racctype
        JOIN tb_dpwd
        ON(tb_member.MBR_ID = tb_racc.ACC_MBR
        AND tb_dpwd.DPWD_MBR = tb_member.MBR_ID
        AND tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
        AND tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        WHERE tb_dpwd.DPWD_STS = 0
        AND tb_dpwd.DPWD_STSVER IN (0, -1)
        AND tb_dpwd.DPWD_STSACC = -1
        AND tb_dpwd.DPWD_TYPE = 1
    ');

    $dt->hide('JSNDT');

    $dt->edit('DPWD_AMOUNT', function($data){
        return '
            <div class="text-end">
                '.$data['RTYPE_CURR_SYMBOL'].'. '.number_format(($data["DPWD_AMOUNT"] ?? 0), 2).'
            </div>
        ';
    });

    $dt->edit('DPWD_PIC', function($data){
        if(!empty($data["DPWD_PIC"])){
            return '
                <div class="text-center">
                    <a target="_blank" href="'.FileUploadFactory::aws()->awsFile($data["DPWD_PIC"]).'">Open</a>
                </div>
            ';
        }
    });

    $dt->edit('ID_DPWD', function($data){
        return '
            <div class="text-center">
                <button type="button" class="btn btn-info edt-btn" data-bs-toggle="modal" data-bs-target="#myModalAuth" data-jsn="'.base64_encode($data["JSNDT"]).'">Detail</button>
            </div>
        ';
    });

    echo $dt->generate()->toJson();