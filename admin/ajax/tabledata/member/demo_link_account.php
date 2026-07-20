<?php

    $dt->query('
        SELECT
            mt4_users.registration,
            mt4_users.LOGIN,
            tb_member.MBR_NAME AS ACC_FULLNAME,
            tb_member.MBR_EMAIL
        FROM tb_racc
        JOIN tb_member
        JOIN mt4_users
        ON(tb_racc.ACC_MBR = tb_member.MBR_ID
        AND tb_racc.ACC_LOGIN = mt4_users.LOGIN)
        WHERE tb_racc.ACC_DERE = 2
        AND tb_member.MBR_STS != 1
    ');

    $dt->edit('registration', function($data){

        return '
            <div class="text-center">
                '.$data["registration"].'
            </div>
        ';

    });

    echo $dt->generate()->toJson();