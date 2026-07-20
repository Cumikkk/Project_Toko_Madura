<?php
    use App\Models\Regol;
    
    $dt->query('
        SELECT
            tb_tst.X,
            tb_tst.TGL,
            tb_tst.ACC_F_APP_PRIBADI_NAMA,
            (
                SELECT
                    IF(JSON_CONTAINS(JSON_ARRAYAGG(tb_apuppt_evcannas.EVCAN_VAL), 1) = 1, "Yes",
                        IF(JSON_CONTAINS(JSON_ARRAYAGG(tb_apuppt_evcannas.EVCAN_VAL), 1) = 0, "No",
                            IF(JSON_CONTAINS(JSON_ARRAYAGG(tb_apuppt_evcannas.EVCAN_VAL), 1) IS NULL, NULL, NULL)
                        )
                    )
                FROM tb_apuppt_evcannas
                WHERE tb_apuppt_evcannas.EVCAN_MBR = tb_tst.ACC_MBR
                AND tb_apuppt_evcannas.EVCAN_TYPE IN(1, 2)
                ORDER BY tb_apuppt_evcannas.EVCAN_DATETIME DESC
                LIMIT 1
            ) AS DTDP,
            (
                SELECT
                    IF(tb_apuppt_evcannas.EVCAN_VAL = 1, "Yes", "No")
                FROM tb_apuppt_evcannas
                WHERE tb_apuppt_evcannas.EVCAN_MBR = tb_tst.ACC_MBR
                AND tb_apuppt_evcannas.EVCAN_TYPE = 4
                ORDER BY tb_apuppt_evcannas.EVCAN_DATETIME DESC
                LIMIT 1
            ) AS PEP,
            tb_tst.MBR_EMAIL,
            tb_tst.ACC_LOGIN,
            tb_tst.ACC_STATUS,
            (
                SELECT
                    (
                        SELECT
                        CONCAT(tb_range.RNG_LEVEL,"(", CAST(SUM((tb_rangensb.NSBR_VAL * tb_rangetype.RATYP_BBR)) AS SIGNED), ")")
                        FROM tb_range
                    WHERE tb_range.RNG_TYPE = 2
                    AND SUM((tb_rangensb.NSBR_VAL * tb_rangetype.RATYP_BBR)) >= tb_range.RNG_MIN
                    AND SUM((tb_rangensb.NSBR_VAL * tb_rangetype.RATYP_BBR)) <= CAST(CASE WHEN tb_range.RNG_MAX = -1 THEN ~0 ELSE tb_range.RNG_MAX END AS UNSIGNED)
                    )
                FROM tb_apuppt
                JOIN tb_rangensb
                JOIN tb_rangetype
                ON(tb_apuppt.APU_RNGNSB_VAL = tb_rangensb.ID_NSBR
                AND tb_apuppt.APU_RNGNSB = tb_rangensb.NSBR_TYPE
                AND tb_rangensb.NSBR_TYPE = tb_rangetype.ID_RATYP)
                WHERE tb_apuppt.APU_ACC = tb_tst.X
                #GROUP BY tb_apuppt.ID_APU
            ) AS RESK,
            tb_tst.IDX AS MRX,
            tb_tst.ACC_STS
        FROM (
            SELECT
                @X := tb_racc.ID_ACC AS X,
                IFNULL(tb_apuppt.APU_DATETIME, tb_racc.ACC_F_PROFILE_DATE) AS TGL,
                tb_racc.ACC_FULLNAME AS ACC_F_APP_PRIBADI_NAMA,
                tb_racc.ACC_NO_IDT AS ACC_F_APP_PRIBADI_ID,
                tb_racc.ACC_TANGGAL_LAHIR AS ACC_F_APP_PRIBADI_TGLLHR,
                tb_racc.ACC_MBR,
                LOWER(tb_member.MBR_EMAIL) AS MBR_EMAIL,
                tb_racc.ACC_LOGIN,
                tb_racc.ACC_WPCHECK AS ACC_STATUS,
                IFNULL(
                    MD5(MD5(MD5(MD5(tb_apuppt.APU_ACC)))), MD5(MD5(tb_racc.ID_ACC))
                ) AS MRX,
                MD5(MD5(tb_racc.ID_ACC)) AS IDX, 
                tb_racc.ACC_STS
            FROM tb_racc
            JOIN tb_member 
            ON (tb_racc.ACC_MBR = tb_member.MBR_ID)
            LEFT JOIN tb_apuppt ON(tb_apuppt.APU_MBR = tb_member.MBR_ID AND tb_apuppt.APU_ACC = tb_racc.ID_ACC)
            WHERE tb_racc.ACC_DERE = 1
            #AND tb_racc.ACC_F_DISC = 1
            AND (tb_racc.ACC_STS != 0 OR tb_racc.ACC_STS != 2)
            GROUP BY tb_racc.ID_ACC
        ) tb_tst
    ');
    
    $dt->hide('X');
    $dt->hide('ACC_STS');

    $dt->edit("ACC_STATUS", fn($col): string => (($col["ACC_STS"] == -1) ? "<span class='badge bg-warning h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px; background: orange ;'>Active</span>" : Regol::wpCheckStatus($col['ACC_STATUS'])['html']));

    $dt->edit('MRX', function($data){
        return '
            <div>
                <a href="/apuppt/evaluasi_nasabah/evaluasi/'.$data["MRX"].'" class="btn btn-sm btn-primary">Evaluasi</a>
            </div>
        ';
    });
    echo $dt->generate()->toJson();