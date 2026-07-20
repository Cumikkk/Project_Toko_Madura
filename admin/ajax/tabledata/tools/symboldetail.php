<?php
$dt->query("
    SELECT
        tb_symbolcat.SYMCAT_NAME AS KATEGORI,
        tb_symbol.SYM_NAME AS SYMBOL,
        MD5(MD5(tb_symbol.ID_SYM)) AS X,
        JSON_OBJECT(
            'edit_type', tb_symbolcat.ID_SYMCAT,
            'symbol_name', tb_symbol.SYM_NAME,
            'xdt', MD5(MD5(tb_symbol.ID_SYM))
        ) AS JSNDT
    FROM tb_symbolcat
    JOIN tb_symbol ON(tb_symbol.ID_SYMCAT = tb_symbolcat.ID_SYMCAT)
");

$dt->hide('JSNDT');

$dt->edit('X', function($data){
    return '
        <button type="button" class="btn btn-success btn-sm edt-btn" data-jsn="'.base64_encode($data["JSNDT"]).'" data-bs-toggle="modal" data-bs-target="#modal-edit-symbol">Edit</button>
        <button type="button" class="btn btn-danger btn-sm hps-btn" data-xid="'.$data["X"].'" data-nme="'.$data["SYMBOL"].'">Hapus</button>
    ';
});

echo $dt->generate()->toJson();