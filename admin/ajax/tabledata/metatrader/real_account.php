<?php
use Config\Core\SystemInfo;

$dbmetasrv = SystemInfo::app('DB_METALIVE');
$dt->query('
    SELECT
        MD5(MD5(tb_racc.ID_ACC)) AS ID_ACC,
        tb_member.MBR_CODE,
        (mt4_users.registration) AS REGDATE,
        mt4_users.login AS LOGIN,
        mt4_users.`name` AS `NAME`,
        mt4_users.email AS EMAIL,
        mt4_users.balance AS BALANCE,
        mt4_users.credit AS CREDIT,
        mt4_users.balance_prevday AS PREVBALANCE,
        mt4_users.balance_prevmonth AS PREVMONTHBALANCE
    FROM mt4_users
    JOIN tb_racc ON (tb_racc.ACC_LOGIN = mt4_users.login)
    JOIN tb_member ON (tb_racc.ACC_MBR = tb_member.MBR_ID)
    WHERE tb_racc.ACC_DERE = 1
');
$dt->hide('ID_ACC');
$dt->hide('MBR_CODE');

$dt->edit('LOGIN', function($data){
    return '<a href="/account/active_real_account/document/'.$data['ID_ACC'].'" target="_blank">'.$data['LOGIN'].'</a>';
});

$dt->edit('EMAIL', function($data){
    return '<a href="/member/user/update/'.$data['MBR_CODE'].'" target="_blank">'.$data['EMAIL'].'</a>';
});

echo $dt->generate()->toJson();