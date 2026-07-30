<?php
require_once __DIR__ . "/../../../../config/setting.php";
use Config\Core\Database;

header('Content-Type: application/json');
$db = Database::connect();

$idOutlet = (int)($_POST['id_outlet'] ?? $_GET['id_outlet'] ?? 0);
if ($idOutlet <= 0) {
    JsonResponse(['success' => false, 'message' => 'ID Outlet tidak valid']);
}

$update = $db->query("UPDATE outlet SET status = 'active', tanggal_disetujui = NOW(), tgl_jatuh_tempo = DATE_ADD(NOW(), INTERVAL 1 MONTH) WHERE id_outlet = {$idOutlet}");
if ($update) {
    JsonResponse(['success' => true, 'message' => 'Request outlet & pembayaran berhasil disetujui. Outlet kini resmi aktif!']);
} else {
    JsonResponse(['success' => false, 'message' => 'Gagal mengaktifkan outlet: ' . $db->error]);
}
