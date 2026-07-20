<?php
    $dt->query("
        SELECT
            DTC_DATETIME,
            DTC_ID,
            DTC_CASE,
            DTC_DESC,
            DTC_PRECONDITION,
            DTC_STEPS,
            DTC_RESULT,
            DTC_ACTUAL_RESULT,
            DTC_NOTES,
            DTC_STS,
            MD5(MD5(ID_DTC)) as ID_DTC,
            JSON_OBJECT(
                'edit_case', DTC_CASE,
                'edit_desc', DTC_DESC,
                'edit_precondition', DTC_PRECONDITION,
                'edit_steps', DTC_STEPS,
                'edit_result', DTC_RESULT,
                'edit_actual_result', DTC_ACTUAL_RESULT,
                'edit_notes', DTC_NOTES,
                'edit_status', DTC_STS,
                'submit-edit-test', CAST(MD5(MD5(ID_DTC)) AS CHAR)
            ) AS JSNDT
        FROM tb_dtc
    ");

    $dt->hide('JSNDT');

    $dt->edit('DTC_STS', function($data) {
        if($data['DTC_STS'] == -1) {
            return "<span class='badge bg-success'>Berhasil</span>";
        }else {
            return "<span class='badge bg-danger'>Gagal</span>";
        }
    });

    $dt->edit('ID_DTC', function($data) {
        return "
            <a href='javascript:void(0)' class='btn btn-success btn-sm edt-btn' data-bs-toggle='modal' data-jsn='".base64_encode($data["JSNDT"])."' data-bs-target='#modalEditTest'>Edit</a>
            <a href='javascript:void(0)' data-value='".$data["ID_DTC"]."' class='btn btn-danger btn-sm dltBtn'>Hapus</a>
        ";
    });

    echo $dt->generate()->toJson();