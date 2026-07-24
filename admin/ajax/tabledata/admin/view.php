<?php
use App\Models\Admin;

$loggedInUser = Admin::authentication();
$loggedInLevel = intval($loggedInUser['ADM_LEVEL'] ?? 1);

$whereClause = "WHERE role = 'master'";
if ($loggedInLevel == 2) {
    // Master Owner: Only show Admin Staff (exclude users with permissions for module_id 4, 5, 6)
    $whereClause .= " AND id_users NOT IN (
        SELECT DISTINCT aa.admin_id 
        FROM admin_authorize aa 
        JOIN admin_permissions ap ON (ap.id = aa.permission_id) 
        WHERE ap.module_id IN (4, 5, 6) AND (aa.status = -1 OR aa.status = 1)
    )";
}

$dt->query("
    SELECT
        NOW() as ADM_TIMESTAMP,
        username as ADM_USER,
        nama_lengkap as ADM_NAME,
        'Master' as ADMROLE_NAME,
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
    global $db;
    $id = intval($data['ID_ADM']);
    $sqlPerms = $db->query("
        SELECT ap.module_id 
        FROM admin_authorize aa 
        JOIN admin_permissions ap ON (ap.id = aa.permission_id) 
        WHERE aa.admin_id = {$id} AND (aa.status = -1 OR aa.status = 1)
    ");
    $modIds = [];
    if ($sqlPerms && $sqlPerms->num_rows > 0) {
        while ($pRow = $sqlPerms->fetch_assoc()) {
            $modIds[] = intval($pRow['module_id']);
        }
    }
    if (in_array(5, $modIds) || in_array(6, $modIds)) {
        return "<span class='badge bg-danger'>Programmer</span>";
    } elseif (in_array(4, $modIds)) {
        return "<span class='badge bg-primary'>Master (Owner)</span>";
    } else {
        return "<span class='badge bg-info'>Admin Staf</span>";
    }
});

$dt->edit('ADM_STS', function($data) {
    return "<span class='badge bg-success'>Active</span>";
});

$dt->edit('ADM_ID', function ($data) {
    return "<div class='action d-flex justify-content-center gap-2' data-id='".$data['ID_ADM']."'></div>";
});

echo $dt->generate()->toJson();