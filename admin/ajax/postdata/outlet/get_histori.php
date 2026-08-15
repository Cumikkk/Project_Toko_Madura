<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$idOutlet = intval($data['id_outlet'] ?? 0);

$db = Database::connect();
$riwayat = [];
$resRiwayat = $db->query("SELECT * FROM riwayat_langganan WHERE id_outlet = {$idOutlet} ORDER BY id_riwayat DESC");
if ($resRiwayat) {
    while ($r = $resRiwayat->fetch_assoc()) {
        $riwayat[] = $r;
    }
}

$outletInfo = null;
$resOutlet = $db->query("SELECT o.id_outlet, o.nama_outlet, o.tgl_jatuh_tempo, o.status, u.nama_lengkap as nama_investor, u.username as username_investor
    FROM outlet o
    LEFT JOIN investor i ON o.id_investor = i.id_investor
    LEFT JOIN users u ON i.id_users = u.id_users
    WHERE o.id_outlet = {$idOutlet} LIMIT 1");
if ($resOutlet && $resOutlet->num_rows > 0) {
    $outletInfo = $resOutlet->fetch_assoc();
}

JsonResponse([
    'code' => 200,
    'success' => true,
    'outlet' => $outletInfo,
    'data' => $riwayat
]);
exit;
