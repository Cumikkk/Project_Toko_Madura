<?php
    $dt->query('
        SELECT
            tb_log.LOG_DATETIME,
            (
                SELECT
                    tb_member.MBR_NAME
                FROM tb_member
                WHERE tb_member.MBR_ID = tb_log.LOG_MBR
                LIMIT 1
            ) AS MBR_NAME,
            (
                SELECT
                    tb_member.MBR_NAME
                FROM tb_member
                WHERE tb_member.MBR_ID = tb_log.LOG_MBR
                LIMIT 1
            ) AS MBR_NAME2,
            (
                SELECT
                    tb_member.MBR_EMAIL
                FROM tb_member
                WHERE tb_member.MBR_ID = tb_log.LOG_MBR
                LIMIT 1
            ) AS MBR_EMAIL,
            tb_log.LOG_DESC,
            tb_log.LOG_IP,
            tb_log.LOG_DEVICE
        FROM tb_log
        WHERE (tb_log.LOG_ADM IS NULL OR tb_log.LOG_ADM = 0)
    ');

    echo $dt->generate()->toJson();