<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
    SELECT
        tb_dormankeep.DRMKEEP_DATETIMEKEEP,
        tb_member.MBR_NAME,
        tb_member.MBR_EMAIL,
        tb_member.MBR_PHONE,
        SUM(mt5users.balance) AS TOTAL_BALANCE,
        tb_dormankeep.DRMKEEP_DATETIMEEXTEND,
        MD5(MD5(tb_dormankeep.ID_DRMKEEP)) AS id
    FROM tb_dormankeep
    JOIN tb_member ON(tb_member.MBR_ID = tb_dormankeep.DRMKEEP_MBRUSER)
    JOIN tb_racc ON(tb_racc.ACC_MBR = tb_member.MBR_ID)
    JOIN ".$dbmetasrv.".mt5_users mt5users ON(mt5users.login = tb_racc.ACC_LOGIN)
    WHERE tb_racc.ACC_DERE = 1
    AND tb_dormankeep.DRMKEEP_MBRSLS = ".$user['MBR_ID']."
    GROUP BY tb_member.MBR_ID
");

echo $dt->generate()->toJson();
?>