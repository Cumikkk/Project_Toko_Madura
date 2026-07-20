<?php
    $dt->query('
        SELECT
            tb_apuppt_evcannas.EVCAN_DATETIME,
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
                AND tb_racc.ACC_WPCHECK >= 1
                LIMIT 1
            ),"Belum DiKonfirmasi") AS STS,
            IF(tb_apuppt_evcannas.EVCAN_CONF = 0, "Ditolak",
                IF(tb_apuppt_evcannas.EVCAN_CONF = 1, "Dipertimbangkan",
                    IF(tb_apuppt_evcannas.EVCAN_CONF = 2, "Dilanjutkan", "Unknown")
                )
            ) AS EVCAN_CONF,
            MD5(MD5(MD5(tb_member.MBR_ID))) AS ID_EVCAN
        FROM tb_member
        JOIN tb_racc
        ON(tb_member.MBR_ID = tb_racc.ACC_MBR)
        JOIN tb_apuppt_evcannas
        ON(tb_member.MBR_ID = tb_apuppt_evcannas.EVCAN_MBR)
        WHERE tb_racc.ACC_DERE = 1
        #AND tb_racc.ACC_STS = 1
        AND tb_racc.ACC_F_DISC4 IS NOT NULL
        GROUP BY tb_member.MBR_ID
    ');
    $dt->edit('ID_EVCAN', function($data){
        return '
            <div class="text-center">
                <a class="btn btn-sm btn-info" href="/apuppt/evaluasi_calon_nasabah/evaluasi/'.$data["ID_EVCAN"].'">Detail</a>
            </div>
        ';
    });
    $dt->edit('EVCAN_CONF', function($data){
        $ARR_CLR = [
            "Ditolak"         => "danger",
            "Dipertimbangkan" => "warning",
            "Dilanjutkan"     => "success",
            "Unknown"         => "secondary"
        ];
        return '
            <div class="text-center">
                <span class="badge bg-'.$ARR_CLR[$data["EVCAN_CONF"]].' h-50 d-inline-block bg-opacity-15 text-white" style="font-size: 12px;">'.$data["EVCAN_CONF"].'</span>
            </div>
        ';
    });
    echo $dt->generate()->toJson();