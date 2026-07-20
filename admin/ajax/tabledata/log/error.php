<?php
    $dt->query('
        SELECT
            tb_log_error.datetime,
            tb_log_error.message,
            tb_log_error.file,
            tb_log_error.line
        FROM tb_log_error
        WHERE tb_log_error.level = 2
    ');

    echo $dt->generate()->toJson();