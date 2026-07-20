<?php
$dt->query("
    SELECT 
        tbi.BECOME_DATETIME,
        tm.MBR_NAME,
        tm.MBR_EMAIL,
        tm.MBR_IDSPN,
        acc.ACCOUNTS_ID,
        tbi.ID_BECOME,
        tm1.MBR_TYPE AS PARENT_TYPE
    FROM tb_become_ib tbi
    JOIN tb_member tm ON (tm.MBR_ID = tbi.BECOME_MBR)
    LEFT JOIN (
        SELECT
            tr.ACC_MBR,
            GROUP_CONCAT(CONCAT(tr.ID_ACC, '.', tr.ACC_LOGIN) SEPARATOR ',') as ACCOUNTS_ID
        FROM tb_racc tr
        WHERE tr.ACC_DERE = 1 
        AND tr.ACC_WPCHECK = 6
        AND tr.ACC_STS = -1
        GROUP BY tr.ACC_MBR
    ) as acc ON (acc.ACC_MBR = tm.MBR_ID)
    LEFT JOIN tb_member tm1 ON(tm1.MBR_ID = tm.MBR_IDSPN)
    WHERE BECOME_STS = 0
");
$dt->hide("MBR_IDSPN");

$dt->edit("ACCOUNTS_ID", function($col) {
    if(!$col['ACCOUNTS_ID']) return '-';
    $list = [];
    $accounts = (explode(",", $col['ACCOUNTS_ID']) ?? []);
    foreach($accounts as $acc) {
        $detail = explode(".", $acc);
        $idAcc = (isset($detail[0])) ? md5(md5($detail[0])) : "-";
        $accLogin = $detail[1];
        $list[] = '<a href="/account/active_real_account/document/'.$idAcc.'">'.$accLogin.'</a>';
    }

    return implode(", ", $list);
});

$dt->edit("ID_BECOME", function($col) {
    $data = base64_encode(json_encode([
        'id' => md5(md5($col['ID_BECOME'])),
        'parent_type' => $col['PARENT_TYPE'],
        'parent' => $col['MBR_IDSPN'],
        'name' => $col['MBR_NAME'],
        'email' => $col['MBR_EMAIL']
    ]));

    return '<div class="action d-flex justify-content-center gap-2" data-data="'.$data.'"></div>';
});

// $dt->edit("ID_BECOME", function($col) {
//     return '<div class="action d-flex justify-content-center gap-2" data-id="'.md5(md5($col['ID_BECOME'])).'"></div>';
// });

echo $dt->generate()->toJson();