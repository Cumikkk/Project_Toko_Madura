<?php
    $dt->query('
        SELECT
            tb_sales_structure.SLSSTRC_NAME,
            tb_racctype.RTYPE_NAME,
            tb_racctype.RTYPE_RATE,
            tb_racctype.RTYPE_KOMISI,
            tb_symbolcat.SYMCAT_NAME,
            tb_commset.COMMSET_AMOUNT,
            MD5(MD5(tb_commset.ID_COMMSET)) AS X,
            JSON_OBJECT(
                "edt_rebate_structure", tb_commset.COMMSET_SALESCAT,
                "edt_rebate_product", tb_commset.COMMSET_PRODUCT,
                "edt_rebate_symbolcat", tb_commset.COMMSET_SYMCAT,
                "edt_rebate_amount", tb_commset.COMMSET_AMOUNT,
                "edt_rebate_amount", tb_commset.COMMSET_AMOUNT,
                "xedt", MD5(MD5(tb_commset.ID_COMMSET))
            ) AS JSNDT
        FROM tb_commset
        JOIN tb_symbolcat ON(tb_symbolcat.ID_SYMCAT = tb_commset.COMMSET_SYMCAT)
        JOIN tb_racctype ON(tb_racctype.ID_RTYPE = tb_commset.COMMSET_PRODUCT)
        JOIN tb_sales_structure ON(tb_sales_structure.ID_SLSSTRC = tb_commset.COMMSET_SALESCAT)
    ');

    $dt->hide('JSNDT');

    $dt->edit('X', function($data){
        return '
            <div class="text-center">
                <button type="button" class="btn btn-success btn-sm edt-btn" data-jsn="'.base64_encode($data["JSNDT"]).'" data-bs-toggle="modal" data-bs-target="#modal-update-rebate">Edit</button>
                <button 
                    type="button"
                    class="btn btn-danger btn-sm hps-btn" 
                    data-val="'.$data["X"].'" 
                    data-spc="'.$data["SLSSTRC_NAME"].'/'.$data["RTYPE_NAME"].'/'.number_format(($data["RTYPE_RATE"] ?? 0), 0).'/'.$data["RTYPE_KOMISI"].'/'.$data["SYMCAT_NAME"].'/'.number_format(($data["COMMSET_AMOUNT"] ?? 0), 2).'"
                >
                    Hapus
                </button>
            </div>
        ';
    });

    echo $dt->generate()->toJson();