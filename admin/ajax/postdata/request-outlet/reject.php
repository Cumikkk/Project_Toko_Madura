<?php
require_once __DIR__ . "/../../../../config/setting.php";
use Config\Core\Database;

header('Content-Type: application/json');
$db = Database::connect();

$idOutlet = (int)($_POST['id_outlet'] ?? 0);
$alasan   = trim($_POST['alasan_penolakan'] ?? '');

if ($idOutlet <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Outlet tidak valid']);
    exit;
}

$escapedAlasan = $db->real_escape_string($alasan);
$update = $db->query("UPDATE outlet SET status = 'reject', alasan_penolakan = '{$escapedAlasan}', tgl_ditolak = NOW() WHERE id_outlet = {$idOutlet}");
if ($update) {
    echo json_encode(['success' => true, 'message' => 'Request outlet & pembayaran berhasil ditolak.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menolak outlet: ' . $db->error]);
}
