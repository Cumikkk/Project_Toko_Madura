<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query("
    SELECT
        COUNT(ip) AS TOTALIP,
        ip AS IP,
        MD5(MD5(ip)) AS HIDIP
    FROM {$dbmetasrv}.mt5_users
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = mt5_users.login)
    WHERE ip <> ''
    GROUP BY ip
    HAVING TOTALIP > 1
");

echo $dt->generate()->toJson();