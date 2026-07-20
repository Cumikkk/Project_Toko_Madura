<?php
use App\Models\Helper;
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$data = Helper::getSafeInput($_GET);
$dt->query("
    SELECT
        login AS LOGIN,
        tb_member.MBR_NAME AS `NAME`,
        FROM_UNIXTIME(lastaccess) AS LASTACCESS
    FROM {$dbmetasrv}.mt5_users
    JOIN tb_racc ON(tb_racc.ACC_LOGIN = mt5_users.login)
    JOIN tb_member ON(tb_member.MBR_ID = tb_racc.ACC_MBR)
    WHERE MD5(MD5(mt5_users.ip)) = '".$data['ip']."'
");

echo $dt->generate()->toJson();