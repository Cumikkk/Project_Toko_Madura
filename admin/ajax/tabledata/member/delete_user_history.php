<?php
    $dt->query('
        SELECT
            tb_dlt_account.DLTACC_DATETIME,
            tb_dlt_account.DLTACC_NAMLENG,
            tb_dlt_account.DLTACC_ACCOUNT,
            tb_dlt_account.DLTACC_NOREK_NSBH,
            tb_dlt_account.DLTACC_NOIDT,
            tb_dlt_account.DLTACC_EMAIL,
            tb_dlt_account.DLTACC_NOTELP,
            tb_dlt_account.DLTACC_LST_EQT,
            tb_dlt_account.DLTACC_STS
        FROM tb_dlt_account
        WHERE tb_dlt_account.DLTACC_STS != 0
    ');

    $dt->edit('DLTACC_STS', function($data){
        if($data["DLTACC_STS"] == -1){
            return '
                <div class="text-center">
                    <span class="badge bg-success">Accept</span>
                </div>
            ';
        }else if($data["DLTACC_STS"] == 1){
            return '
                <div class="text-center">
                    <span class="badge bg-danger">Reject</span>
                </div>
            ';
        }else {
            return '
                <div class="text-center">
                    <span class="badge bg-secondary">Unknown</span>
                </div>
            ';
        }
    });

    echo $dt->generate()->toJson();