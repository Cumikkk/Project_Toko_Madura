<?php
$dt->query('
    SELECT
        ADM_TIMESTAMP,
        ADM_USER,
        ADM_NAME,
        ADMROLE_NAME,
        ADM_LEVEL,
        ADM_STS,
        ADM_PASS,
        ID_ADM,
        ADM_ID,
        tc.COUNTRY_NAME
    FROM tb_admin
    JOIN tb_admin_role tar ON (tar.ID_ADMROLE = ADM_LEVEL)
    JOIN tb_country tc ON (tc.ID_COUNTRY = ADM_COUNTRY)
    WHERE tb_admin.ADM_LEVEL > '.$user['ADM_LEVEL'].'
    AND ADM_STS = -1
');

$dt->hide('ID_ADM');
$dt->hide('ADM_PASS');
$dt->hide('ADM_LEVEL');
$dt->hide('COUNTRY_NAME');
$dt->edit('ADM_STS', function($data) {
    switch($data['ADM_STS']) {
        case 0: return "<span class='badge bg-warning'>Pending</span>";
        case -1: return "<span class='badge bg-success'>Active</span>";
        case 1: return "<span class='badge bg-danger'>Inactive</span>";
    }
});

$dt->edit('ADM_ID', function ($data) {
    return "<div class='action d-flex justify-content-center gap-2' data-id='".$data['ID_ADM']."'></div>";
});


echo $dt->generate()->toJson(); // same as 'echo $dt->generate()';