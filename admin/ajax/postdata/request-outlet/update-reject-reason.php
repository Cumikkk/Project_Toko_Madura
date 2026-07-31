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

if (empty($alasan)) {
    echo json_encode(['success' => false, 'message' => 'Alasan penolakan tidak boleh kosong']);
    exit;
}

$escapedAlasan = $db->real_escape_string($alasan);
$update = $db->query("UPDATE outlet SET alasan_penolakan = '{$escapedAlasan}' WHERE id_outlet = {$idOutlet} AND status = 'reject'");

if ($update) {
    echo json_encode(['success' => true, 'message' => 'Alasan penolakan outlet berhasil diperbarui.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui alasan penolakan: ' . $db->error]);
}
