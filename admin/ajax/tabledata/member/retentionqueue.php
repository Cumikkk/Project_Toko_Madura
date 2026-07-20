<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
SELECT
	tb_dormanconfirm.DRMCONFIRM_DATETIME,
	tbmember_sls.MBR_NAME AS SALES_NAME,
	tbmember_sls.MBR_EMAIL AS SALES_EMAIL,
	tbmember_user.MBR_NAME AS USER_NAME,
	tbmember_user.MBR_EMAIL AS USER_EMAIL,
	ROUND(IFNULL(SUM(mt5users.balance), 0), 2) AS BALANCE,
	MD5(MD5(tb_dormanconfirm.ID_DRMCONF)) AS ID_DRMCONF
FROM tb_dormanconfirm
JOIN tb_member tbmember_user ON(tbmember_user.MBR_ID = tb_dormanconfirm.DRMCONFIRM_MBRUSER)
JOIN tb_member tbmember_sls ON(tbmember_sls.MBR_ID = tb_dormanconfirm.DRMCONFIRM_MBRSLS)
JOIN tb_racc ON(tb_racc.ACC_MBR = tbmember_user.MBR_ID)
JOIN {$dbmetasrv}.mt5_users mt5users ON(mt5users.login = tb_racc.ACC_LOGIN)
WHERE tb_racc.ACC_DERE = 1
AND tb_dormanconfirm.DRMCONFIRM_STS = 0
GROUP BY tbmember_user.MBR_ID
");

$dt->edit("ID_DRMCONF", function($col) {
    $data = base64_encode(json_encode([
        'id' => $col['ID_DRMCONF']
    ]));

    return '<div class="action d-flex justify-content-center gap-2" data-id="'.$col['ID_DRMCONF'].'"></div>';
});

echo $dt->generate()->toJson();