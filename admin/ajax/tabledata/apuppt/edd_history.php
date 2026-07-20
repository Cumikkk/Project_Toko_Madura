<?php
    
    use App\Models\Helper;

    $x = Helper::form_input($_GET["mrx"]);

    $dt->query($qwr = '
        SELECT
            tb_apuppt_edd.ADD_DATTIME,
            tb_racc.ACC_FULLNAME,
            tb_racc.ACC_NO_IDT,
            (
                SELECT
                    tb_range_edd.EDD_LV
                FROM tb_range_edd
                WHERE JSON_CONTAINS(JSON_ARRAYAGG(tb_apuppt_edd.ADD_VAL), tb_range_edd.ID_EDD)
                #WHERE tb_range_edd.ID_EDD MEMBER OF(JSON_ARRAYAGG(tb_apuppt_edd.ADD_VAL))
                LIMIT 1 OFFSET 0
            ) AS CL1,
            (
                SELECT
                    tb_range_edd.EDD_LV
                FROM tb_range_edd
                WHERE JSON_CONTAINS(JSON_ARRAYAGG(tb_apuppt_edd.ADD_VAL), tb_range_edd.ID_EDD)
                #WHERE tb_range_edd.ID_EDD MEMBER OF(JSON_ARRAYAGG(tb_apuppt_edd.ADD_VAL))
                LIMIT 1 OFFSET 1
            ) AS CL2,
            (
                SELECT
                    tb_range_edd.EDD_LV
                FROM tb_range_edd
                WHERE JSON_CONTAINS(JSON_ARRAYAGG(tb_apuppt_edd.ADD_VAL), tb_range_edd.ID_EDD)
                #WHERE tb_range_edd.ID_EDD MEMBER OF(JSON_ARRAYAGG(tb_apuppt_edd.ADD_VAL))
                LIMIT 1 OFFSET 2
            ) AS CL3,
            tb_apuppt_edd.ADD_ARF,
            tb_apuppt_edd.ADD_RKM,
            MD5(MD5(MD5(tb_apuppt_edd.ADD_HSTRID))) AS ADD_HSTRID
        FROM tb_racc
        JOIN tb_apuppt_edd ON(tb_apuppt_edd.ADD_MBR = tb_racc.ACC_MBR)
        WHERE MD5(MD5(tb_racc.ID_ACC)) = "'.$x.'"
        GROUP BY tb_apuppt_edd.ADD_HSTRID
    ');
    
    $ARR_CLR = [
        "Rendah"   => "success",
        "Menengah" => "warning",
        "Tinggi"   => "danger"
    ];
    $dt->edit('ADD_HSTRID', function($data){
        return '
            <div>
                <a href="/apuppt/edd/evaluasi/'.$data["ADD_HSTRID"].'" class="btn btn-sm btn-primary">Detail</a>
            </div>
        ';
    });
    $dt->edit('CL1', function($data) use ($ARR_CLR){
        return '
            <div class="text-center">
                <span class="badge bg-'.$ARR_CLR[$data["CL1"]].' h-50 d-inline-block bg-opacity-15 text-white" style="font-size: 12px;">'.$data["CL1"].'</span>
            </div>
        ';
    });
    $dt->edit('CL2', function($data) use ($ARR_CLR){
        return '
            <div class="text-center">
                <span class="badge bg-'.$ARR_CLR[$data["CL2"]].' h-50 d-inline-block bg-opacity-15 text-white" style="font-size: 12px;">'.$data["CL2"].'</span>
            </div>
        ';
    });
    $dt->edit('CL3', function($data) use ($ARR_CLR){
        return '
            <div class="text-center">
                <span class="badge bg-'.$ARR_CLR[$data["CL3"]].' h-50 d-inline-block bg-opacity-15 text-white" style="font-size: 12px;">'.$data["CL3"].'</span>
            </div>
        ';
    });
    $dt->edit('ADD_ARF', function($data){
        return '
            <div class="text-center">
                '.$data["ADD_ARF"].'
            </div>
        ';
    });
    $dt->edit('ADD_RKM', function($data){
        return '
            <div class="text-center">
                '.$data["ADD_RKM"].'
            </div>
        ';
    });


    echo $dt->generate()->toJson();