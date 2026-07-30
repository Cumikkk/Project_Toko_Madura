<?php
require_once __DIR__ . "/../../../../config/setting.php";
use Config\Core\Database;

header('Content-Type: application/json');
$db = Database::connect();

$idOutlet = (int)($_POST['id_outlet'] ?? 0);
if ($idOutlet <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Outlet tidak valid']);
    exit;
}

$update = $db->query("UPDATE outlet SET status = 'active', tanggal_disetujui = NOW(), tanggal_bergabung = IFNULL(tanggal_bergabung, NOW()), tgl_jatuh_tempo = DATE_ADD(NOW(), INTERVAL 1 MONTH) WHERE id_outlet = {$idOutlet}");
if ($update) {
    echo json_encode(['success' => true, 'message' => 'Request outlet & pembayaran berhasil disetujui. Outlet kini resmi aktif!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengaktifkan outlet: ' . $db->error]);
}
