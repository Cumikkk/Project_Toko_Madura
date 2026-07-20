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
            MD5(MD5(tb_dlt_account.ID_DLTACC)) AS ID_DLTACC
        FROM tb_dlt_account
        WHERE tb_dlt_account.DLTACC_STS = 0
    ');

    $dt->edit('ID_DLTACC', function($data){
        return '
            <div class="text-center">
                <button type="button" class="btn btn-sm btn-success btn-act" data-xid="'.$data["ID_DLTACC"].'" data-value="accept">Accept</button>
                <button type="button" class="btn btn-sm btn-danger btn-act" data-xid="'.$data["ID_DLTACC"].'" data-value="reject">Reject</button>
            </div>
        ';
    });

    echo $dt->generate()->toJson();