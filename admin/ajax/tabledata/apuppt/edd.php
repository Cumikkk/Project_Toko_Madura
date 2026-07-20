<?php
    
    use Config\Core\SystemInfo;
    $dt->query('
        SELECT
            IXMBR,
            (
                SELECT
                    tb_racc.ACC_DATETIME
                FROM tb_racc
                WHERE JSON_CONTAINS(tb_tst.IXMBR, tb_racc.ACC_MBR)
                AND tb_racc.ACC_DERE = 1
                AND tb_racc.ACC_WPCHECK = 6
                AND (tb_racc.ACC_LOGIN != "0" AND tb_racc.ACC_LOGIN IS NOT NULL)
                LIMIT 1
            ) AS ACC_DATETIME,
            (
                SELECT
                    tb_apuppt_edd.ADD_DATTIME
                FROM tb_apuppt_edd
                #WHERE tb_apuppt_edd.ADD_MBR MEMBER OF(JSON_ARRAYAGG(tb_racc.ACC_MBR))
                WHERE JSON_CONTAINS(tb_tst.IXMBR, tb_apuppt_edd.ADD_MBR)
                GROUP BY tb_apuppt_edd.ADD_HSTRID
                ORDER BY tb_apuppt_edd.ADD_DATTIME DESC
                LIMIT 1
            ) AS LAST_UPDT,
            (
                SELECT
                    IFNULL(tb_racc.ACC_FULLNAME, tb_member.MBR_NAME)
                FROM tb_racc
                JOIN tb_member
                ON (tb_racc.ACC_MBR = tb_member.MBR_ID)
                WHERE JSON_CONTAINS(tb_tst.IXMBR, tb_racc.ACC_MBR)
                AND tb_racc.ACC_DERE = 1
                AND tb_racc.ACC_WPCHECK = 6
                AND (tb_racc.ACC_LOGIN != "0" AND tb_racc.ACC_LOGIN IS NOT NULL)
                LIMIT 1
            ) AS NAMA,
            (
                SELECT
                    tb_racc.ACC_NO_IDT
                FROM tb_racc
                WHERE JSON_CONTAINS(tb_tst.IXMBR, tb_racc.ACC_MBR)
                AND tb_racc.ACC_DERE = 1
                AND tb_racc.ACC_WPCHECK = 6
                AND (tb_racc.ACC_LOGIN != "0" AND tb_racc.ACC_LOGIN IS NOT NULL)
                LIMIT 1
            ) AS ACC_F_APP_PRIBADI_ID,
            (
                SELECT
                    JSON_ARRAYAGG(tb_sub.MBR_EMAIL)
                FROM tb_member tb_sub
                #WHERE tb_sub.MBR_ID MEMBER OF(JSON_ARRAYAGG(tb_racc.ACC_MBR))
                WHERE JSON_CONTAINS(tb_tst.IXMBR, tb_sub.MBR_ID)
            ) AS EMAIL,
            (
                SELECT
                    JSON_ARRAYAGG(tb_racc.ACC_LOGIN)
                FROM tb_racc
                WHERE JSON_CONTAINS(tb_tst.IXMBR, tb_racc.ACC_MBR)
                AND tb_racc.ACC_DERE = 1
                AND tb_racc.ACC_WPCHECK = 6
                AND (tb_racc.ACC_LOGIN != "0" AND tb_racc.ACC_LOGIN IS NOT NULL)
                LIMIT 1	
            ) AS LOGIN,
            (
                SELECT
                    MD5(MD5(tb_racc.ID_ACC))
                FROM tb_racc
                WHERE JSON_CONTAINS(tb_tst.IXMBR, tb_racc.ACC_MBR)
                AND tb_racc.ACC_DERE = 1
                AND tb_racc.ACC_WPCHECK = 6
                AND (tb_racc.ACC_LOGIN != "0" AND tb_racc.ACC_LOGIN IS NOT NULL)
                LIMIT 1	
            ) AS ID_ACC
        FROM (
            SELECT
                @IXMBR := JSON_ARRAYAGG(tb_racc.ACC_MBR) AS IXMBR
            FROM tb_racc
            JOIN tb_member
            ON(tb_racc.ACC_MBR = tb_member.MBR_ID)
            WHERE tb_racc.ACC_DERE = 1
            AND tb_racc.ACC_WPCHECK = 6
            AND (tb_racc.ACC_LOGIN != "0" AND tb_racc.ACC_LOGIN IS NOT NULL)
            GROUP BY tb_racc.ACC_NO_IDT
        ) tb_tst
    ');

    
    $dt->hide('IXMBR');
    $dt->edit('ACC_DATETIME', function($data){
        return '<div class="text-center">'.$data["ACC_DATETIME"].'</div>';
    });
    $dt->edit('LAST_UPDT', function($data){
        return '<div class="text-center">'.$data["LAST_UPDT"].'</div>';
    });
    $dt->edit('EMAIL', function($data){
        return preg_replace('/(\[|\]|")/i', '', $data["EMAIL"]);
    });
    $dt->edit('LOGIN', function($data) use (&$DTACCNTS) {
        return preg_replace('/(\[|\]|")/i', '', $data["LOGIN"]);
    });
    
    $dt->edit('ID_ACC', function($data){
        return '
            <div class="text-center">
                <a href="/apuppt/edd/evaluasi/'.$data["ID_ACC"].'" class="btn btn-sm btn-primary">Evaluasi</a> || 
                <a href="/apuppt/edd/history/'.$data["ID_ACC"].'" class="btn btn-sm btn-success">History</a>
            </div>
        ';
    });

    echo $dt->generate()->toJson();