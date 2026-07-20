<?php
$dt->query("
    SELECT
        SYMCAT_NAME AS SYMBOL,
        JSON_OBJECT(
            'category_name', SYMCAT_NAME,
            'xid', MD5(MD5(ID_SYMCAT))
        ) AS JSNDT
    FROM tb_symbolcat
");

$dt->edit('JSNDT', function($data){
    return '
        <button type="button" class="btn btn-sm btn-success edt-btn" data-jsn="'.base64_encode($data["JSNDT"]).'" data-bs-toggle="modal" data-bs-target="#modal-edit-category">Edit</button>
    ';
});

echo $dt->generate()->toJson();