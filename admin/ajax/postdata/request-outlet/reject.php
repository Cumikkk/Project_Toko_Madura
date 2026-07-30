<?php
require_once __DIR__ . "/../../../../config/setting.php";
use Config\Core\Database;

header('Content-Type: application/json');
$db = Database::connect();

$idOutlet = (int)($_POST['id_outlet'] ?? $_GET['id_outlet'] ?? 0);
$alasan   = trim($_POST['alasan_penolakan'] ?? $_POST['alasan'] ?? '');

if ($idOutlet <= 0) {
    JsonResponse(['success' => false, 'message' => 'ID Outlet tidak valid']);
}

$escapedAlasan = $db->real_escape_string($alasan);
$update = $db->query("UPDATE outlet SET status = 'reject', alasan_penolakan = '{$escapedAlasan}', tanggal_ditolak = NOW() WHERE id_outlet = {$idOutlet}");
if ($update) {
    JsonResponse(['success' => true, 'message' => 'Request outlet & pembayaran berhasil ditolak.']);
} else {
    JsonResponse(['success' => false, 'message' => 'Gagal menolak outlet: ' . $db->error]);
}
