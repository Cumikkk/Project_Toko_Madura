<?php
    $dt->query('
        SELECT
            @no:=@no+1 nomor,
            tb_banklist.BANKLST_NAME AS bank_name,
            tb_banklist.ID_BANKLST AS idb
        FROM tb_banklist,
        (SELECT @no:= 0) AS nomor
    ');
    $dt->edit('idb', function($col) {
        $data = base64_encode(json_encode([
            'idb' => $col['idb'] ?? 0,
            'bank_name' => $col['bank_name'] ?? ''
        ]));
        return '<div class="action text-center" data-data="'.$data.'"></div>';
    });

    echo $dt->generate()->toJson();
?>