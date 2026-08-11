<?php
use App\Models\Admin;

$loggedInUser = Admin::authentication();
$loggedInLevel = intval($loggedInUser['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($loggedInUser['ADM_ID'] ?? 1);

// Role filtering:
// Programmer (Level 1): See all investors nationally
// Master Owner (Level 2): See only investors belonging to him (i.id_master = loggedInId)
// Admin Staff (Level 3): See investors belonging to Master Owner (i.id_master = 2)
if ($loggedInLevel == 1) {
    $whereClause = "";
} elseif ($loggedInLevel == 2) {
    $whereClause = "WHERE i.id_master = {$loggedInId}";
} else {
    // Admin Staff (Level 3): Show Master Owner's investors
    $whereClause = "WHERE i.id_master = 2";
}

$dt->query("
    SELECT
        u.nama_lengkap as INV_NAME,
        u.username as INV_USER,
        u.no_hp as INV_HP,
        u.kecamatan as INV_KECAMATAN,
        u.alamat_lengkap as INV_ALAMAT,
        u_master.nama_lengkap as MASTER_NAME,
        i.id_investor as ID_INV
    FROM investor i
    JOIN users u ON (u.id_users = i.id_users)
    LEFT JOIN users u_master ON (u_master.id_users = i.id_master)
    {$whereClause}
");

$dt->edit('INV_NAME', function($data) {
    return "<strong>" . htmlspecialchars($data['INV_NAME'] ?? '-') . "</strong>";
});

$dt->edit('INV_USER', function($data) {
    return "<code>" . htmlspecialchars($data['INV_USER'] ?? '-') . "</code>";
});

$dt->edit('INV_KECAMATAN', function($data) {
    return htmlspecialchars($data['INV_KECAMATAN'] ?? '-');
});

$dt->edit('MASTER_NAME', function($data) {
    return "<span class='badge bg-info'>" . htmlspecialchars($data['MASTER_NAME'] ?? 'Master Owner') . "</span>";
});

$dt->edit('ID_INV', function ($data) {
    return "<div class='action d-flex justify-content-center gap-2' data-id='".$data['ID_INV']."'></div>";
});

echo $dt->generate()->toJson();
