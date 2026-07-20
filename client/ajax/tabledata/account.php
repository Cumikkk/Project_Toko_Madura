<?php

use App\Models\Regol;
$dt->query("
    SELECT 
        tr.ACC_DATETIME,
        tr.ACC_LOGIN, 
        IFNULL(mt4users.balance, 0) AS balance,
        trc.RTYPE_TYPE,
        trc.RTYPE_CURR,
        IF(trc.RTYPE_TYPE = 'DEMO', '',
            IF(trc.RTYPE_RATE IS NULL OR trc.RTYPE_RATE = 0, 'Floating', trc.RTYPE_RATE)
        ) AS RTYPE_RATE,
        tr.ACC_WPCHECK,
        (
            SELECT
                tb_note.NOTE_NOTE
            FROM tb_note
            WHERE tb_note.NOTE_RACC = tr.ID_ACC
            ORDER BY tb_note.ID_NOTE DESC
            LIMIT 1
        ) AS NOTE_NOTE,
        tr.ACC_LAST_STEP,
        tr.ID_ACC,
        tr.ACC_STS
    FROM tb_racc tr
    JOIN tb_racctype trc ON (trc.ID_RTYPE = tr.ACC_TYPE)
    LEFT JOIN (
        SELECT 
            login,
            balance
        FROM mt4_users
    ) mt4users ON (mt4users.login = tr.ACC_LOGIN)
    WHERE ACC_MBR = ".$user['MBR_ID']."
    GROUP BY tr.ID_ACC
");

$dt->hide("ACC_STS");
$dt->hide("ACC_LAST_STEP");
// $dt->hide("ID_NOTE");
$dt->edit("ACC_DATETIME", function($data) {
    return '<div class="text-center">'.(date("Y-m-d H:i:s", strtotime($data['ACC_DATETIME']))).'</div>';
});

$dt->edit("ACC_WPCHECK", function($data) {
    if(strtoupper($data['RTYPE_TYPE']) == "DEMO") {
        return "-";
    }

    switch($data['ACC_STS']) {
        case 0: return '<a href="/account/create?page='.$data['ACC_LAST_STEP'].'"><span class="badge bg-warning small">Lanjutkan</span></a>';
        case -1: return '<a href="javascript:void(0)"><span class="badge bg-success small">Success</span></a>';
        case 1: return '<a href="javascript:void(0)"><span class="badge bg-secondary small">Waiting for confirm</span></a>';
        case 2: return '<a href="/account/create?page='.$data['ACC_LAST_STEP'].'"><span class="badge bg-danger small">Ditolak</span></a>';
    }
});

$dt->edit("ID_ACC", function($data) {
    if(strtoupper($data['RTYPE_TYPE']) != "DEMO") {
        if($data['ACC_WPCHECK'] != 6 && $data['ACC_STS'] != -1) {
            return '<div class="text-center"></div>';
        }

        return '
            <div class="text-center">
                <a href="/deposit" title="Deposit"><button class="btn btn-success"><i class="fa-light fa-arrow-right-to-bracket"></i></button></a>
                <a href="/withdrawal" title="Withdrawal"><button class="btn btn-danger"><i class="fa-light fa-arrow-right-from-bracket"></i></button></a>
                <a href="/document?id='.(md5(md5($data['ID_ACC'] ?? "-"))).'" title="Documents"><button class="btn btn-info"><i class="fa-light fa-file"></i></button></a>
            </div>
        ';
    }
});

echo $dt->generate()->toJson();