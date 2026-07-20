<?php

$dt->query("
    SELECT 
        tls.DATETIME as created_at,
        tls.SUBJECT as subject,
        tls.RECIPIENT as recipient,
        ta.ADM_NAME as sender,
        MD5(MD5(tls.ID_SENDEMAIL)) as id
    FROM tb_log_sendemail tls
    JOIN tb_admin as ta ON (ta.ADM_ID = tls.REQUEST_BY)
");

$dt->edit("created_at", function($data) {
    return date('Y/m/d H:i:s', strtotime($data['created_at']));
});

echo $dt->generate()->toJson();