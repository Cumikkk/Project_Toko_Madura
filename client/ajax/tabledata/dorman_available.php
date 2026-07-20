<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
SELECT
	tb_member.MBR_NAME,
	tb_member.MBR_EMAIL,
	tb_member.MBR_PHONE,
	ROUND(IFNULL(SUM(mt5users.balance), 0), 2) AS BALANCE
FROM tb_dormanconfirm
JOIN tb_member ON(tb_member.MBR_ID = tb_dormanconfirm.DRMCONFIRM_MBRUSER)
JOIN tb_racc ON(tb_racc.ACC_MBR = tb_member.MBR_ID)
JOIN ".$dbmetasrv.".mt5_users mt5users ON(mt5users.login = tb_racc.ACC_LOGIN)
WHERE tb_racc.ACC_DERE = 1
AND tb_dormanconfirm.DRMCONFIRM_STS = -1
GROUP BY tb_member.MBR_ID
");

echo $dt->generate()->toJson();
?>