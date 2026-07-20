<?php
    $dt->query('
        SELECT
            tb_log.LOG_DATETIME,
            (
                SELECT
                    tb_admin.ADM_NAME
                FROM tb_admin
                WHERE tb_admin.ADM_ID = tb_log.LOG_ADM
                LIMIT 1
            ) AS ADM_NAME,
            (
                SELECT
                    tb_admin.ADM_NAME
                FROM tb_admin
                WHERE tb_admin.ADM_ID = tb_log.LOG_ADM
                LIMIT 1
            ) AS ADM_NAME2,
            tb_log.LOG_DESC,
            tb_log.LOG_IP,
            tb_log.LOG_DEVICE
        FROM tb_log
        WHERE (tb_log.LOG_ADM IS NOT NULL OR tb_log.LOG_ADM != 0)
    ');

    echo $dt->generate()->toJson();