<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
    SELECT
        MAX(DATETIMELAST_TX) AS datetime_tx,
        MBR_NAME AS fullname,
        SUM(balance) AS balance,
        MAX(TIMESTAMPLAST_TX) AS timestamp_tx,
        PROF_DORMAN AS dorman,
        DATEDIFF(NOW(), MAX(DATETIMELAST_TX)) AS diff_date,
        MD5(MD5(MBR_ID)) AS id
    FROM (
        SELECT
            tb_member.MBR_ID,
            tb_member.MBR_NAME,
            mt5users.balance,
            FROM_UNIXTIME(MAX(mt5deals.`time`)) AS DATETIMELAST_TX,
            MAX(mt5deals.`time`) AS TIMESTAMPLAST_TX,
            tb_profile.PROF_DORMAN
        FROM {$dbmetasrv}.mt5_deals mt5deals
        JOIN {$dbmetasrv}.mt5_users mt5users ON(mt5users.login = mt5deals.login)
        JOIN tb_racc ON(tb_racc.ACC_LOGIN = mt5deals.login)
        JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR)
        JOIN tb_profile
        WHERE mt5deals.login NOT IN (
            SELECT mt5positions.login
            FROM {$dbmetasrv}.mt5_positions mt5positions
        )
        AND tb_member.MBR_ID NOT IN (
            SELECT tb_dormankeep.DRMKEEP_MBRUSER
            FROM tb_dormankeep
        )
        AND tb_racc.ACC_DERE = 1
        AND tb_member.MBR_ID NOT IN (
				SELECT tb_dormanconfirm.DRMCONFIRM_MBRUSER
				FROM tb_dormanconfirm
		  )
        GROUP BY mt5deals.login
        HAVING TIMESTAMPLAST_TX < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - PROF_DORMAN DAY))
    ) tb1
    GROUP BY MBR_ID
");
$dt->hide("timestamp_tx");
$dt->hide("dorman");

echo $dt->generate()->toJson();