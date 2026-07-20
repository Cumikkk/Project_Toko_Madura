<?php
    $dt->query('
        SELECT
            tb_member.MBR_DATETIME,
            tb_member.MBR_NAME,
            IFNULL(tb_racc.ACC_NO_IDT, tb_member.MBR_NO_IDT) ACC_F_APP_PRIBADI_ID,
            IFNULL(DATE(tb_racc.ACC_TANGGAL_LAHIR), DATE(tb_member.MBR_TGLLAHIR)) AS MBR_TGLLAHIR,
            tb_member.MBR_EMAIL,
            IFNULL((
                SELECT
                    "Sudah Dikonfirmasi"
                FROM tb_racc
                WHERE tb_racc.ACC_DERE = 1
                AND tb_racc.ACC_MBR = tb_member.MBR_ID
                #AND tb_racc.ACC_WPCHECK >= 1
                AND tb_racc.ACC_STS = -1
                LIMIT 1
            ),"Belum DiKonfirmasi") AS STS,
            MD5(MD5(tb_member.MBR_ID)) AS MBR_ID
        FROM tb_member
        JOIN tb_racc
        ON(tb_member.MBR_ID = tb_racc.ACC_MBR)
        WHERE tb_racc.ACC_DERE = 1
        #AND tb_racc.ACC_STS = 1
        AND tb_racc.ACC_F_DISC4 IS NOT NULL
        AND NOT EXISTS(SELECT 1 FROM tb_apuppt_evcannas WHERE tb_apuppt_evcannas.EVCAN_MBR = tb_racc.ACC_MBR)
    ');

    $dt->edit('MBR_ID', function($data){
        return  '
            <div class="text-center">
                <a class="btn btn-sm btn-info" href="/apuppt/evaluasi_calon_nasabah/evaluasi/'.$data["MBR_ID"].'">Detail</a>
            </div>
        ';
    });

    echo $dt->generate()->toJson();