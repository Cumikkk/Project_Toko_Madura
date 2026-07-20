<?php
    $dt->query('
        SELECT
            tb_wpb.WPB_NAMA,
            tb_wpb.WPB_TYPE,
            tb_wpb.WPB_STS,
            MD5(MD5(tb_wpb.ID_WPB)) AS ID_WPB,
            tb_wpb.WPB_VERIFY,
            JSON_OBJECT(
                "edt_nama", WPB_NAMA,
                "edt_type", WPB_TYPE,
                "edt_stat", WPB_STS,
                "edt_wpb_no1", WPB_NO1,
                "edt_wpb_no2", WPB_NO2,
                "edt_idnt", CAST(MD5(MD5(tb_wpb.ID_WPB)) AS  CHAR),
                "ttd", WPB_TANDA_TANGAN
            ) AS JSNDT
        FROM tb_wpb
    ');
    $dt->hide('WPB_VERIFY');
    $dt->hide('JSNDT');

    $dt->edit('WPB_TYPE', function($data){
        $TYPE = [
            1 => "WPB Perusahaan",
            2 => "WPB Yang Di Tunjuk Untuk Verifikasi"
        ];

        return $TYPE[$data["WPB_TYPE"]] ?? 'Unknown';
    });

    $dt->edit('WPB_STS', function($data){
        $TYPE = [
            0 => "Unactive",
            -1 => "Active"
        ];

        return $TYPE[$data["WPB_STS"]] ?? 'Unknown';
    });

    $dt->edit('ID_WPB', function($data){
        switch ($data["WPB_TYPE"]) {
            case 2:
                return '
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-secondary verfBtn" data-name="'.$data["WPB_NAMA"].'" data-value="'.$data["ID_WPB"].'">'.(($data["WPB_VERIFY"] == -1) ? '<strong>Ditunjuk</strong>' : 'Tunjuk').'</button>
                        <button type="button" class="btn btn-sm btn-info edt-btn" data-jsn="'.base64_encode($data["JSNDT"]).'" data-bs-toggle="modal" data-bs-target="#modalEdtWpb">Update Data</button>
                        <button type="button" class="btn btn-sm btn-danger dltBtn" data-value="'.$data["ID_WPB"].'">Hapus</button>
                    </div>
                ';
            
            default:
            return '
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-info edt-btn" data-jsn="'.base64_encode($data["JSNDT"]).'" data-bs-toggle="modal" data-bs-target="#modalEdtWpb">Update Data</button>
                    <button type="button" class="btn btn-sm btn-danger">Hapus</button>
                </div>
            ';
        }
    });

    echo $dt->generate()->toJson();