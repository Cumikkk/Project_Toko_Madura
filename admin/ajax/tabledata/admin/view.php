<?php
use App\Models\Admin;

$loggedInUser = Admin::authentication();
$loggedInLevel = intval($loggedInUser['ADM_LEVEL'] ?? 1);

$whereClause = "WHERE role = 'admin'";

$dt->query("
    SELECT
        NOW() as ADM_TIMESTAMP,
        username as ADM_USER,
        nama_lengkap as ADM_NAME,
        role as ADMROLE_NAME,
        1 as ADM_LEVEL,
        -1 as ADM_STS,
        password as ADM_PASS,
        id_users as ID_ADM,
        id_users as ADM_ID,
        'Indonesia' as COUNTRY_NAME
    FROM users
    {$whereClause}
");

$dt->hide('ID_ADM');
$dt->hide('ADM_PASS');
$dt->hide('ADM_LEVEL');
$dt->hide('COUNTRY_NAME');

$dt->edit('ADMROLE_NAME', function($data) {
    $r = strtolower($data['ADMROLE_NAME'] ?? '');
    if ($r === 'admin') {
        return "<span class='badge bg-danger'>Admin</span>";
    } else {
        return "<span class='badge bg-primary'>Master (Owner)</span>";
    }
});

$dt->edit('ADM_STS', function($data) {
    return "<span class='badge bg-success'>Active</span>";
});

$dt->edit('ADM_ID', function ($data) {
    return "<div class='action d-flex justify-content-center gap-2' data-id='".$data['ID_ADM']."'></div>";
});

echo $dt->generate()->toJson();