<?php
require_once __DIR__ . "/../../../../config/setting.php";
use Config\Core\Database;

header('Content-Type: application/json');
$db = Database::connect();

$type = trim($_POST['setting_type'] ?? '');
$updates = [];

if ($type === 'biaya_langganan' || isset($_POST['biaya_langganan_outlet'])) {
    $biayaLangganan = (float)($_POST['biaya_langganan_outlet'] ?? 0);
    $updates['biaya_langganan_outlet'] = $biayaLangganan;
}

if ($type === 'rekening_bank' || isset($_POST['bank_nama'])) {
    $bankNama     = trim($_POST['bank_nama'] ?? '');
    $bankNoRek    = trim($_POST['bank_no_rekening'] ?? '');
    $bankAtasNama = trim($_POST['bank_atas_nama'] ?? '');

    if (empty($bankNama) || empty($bankNoRek) || empty($bankAtasNama)) {
        echo json_encode(['success' => false, 'message' => 'Harap lengkapi semua informasi rekening bank!']);
        exit;
    }

    $updates['bank_nama']       = $bankNama;
    $updates['bank_no_rekening'] = $bankNoRek;
    $updates['bank_atas_nama']  = $bankAtasNama;
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada data pengaturan yang dikirim']);
    exit;
}

foreach ($updates as $key => $val) {
    $escapedVal = $db->real_escape_string($val);
    $chk = $db->query("SELECT id_pengaturan FROM pengaturan_sistem WHERE nama_pengaturan = '{$key}' LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
        $db->query("UPDATE pengaturan_sistem SET nilai = '{$escapedVal}' WHERE nama_pengaturan = '{$key}'");
    } else {
        $db->query("INSERT INTO pengaturan_sistem (nama_pengaturan, nilai) VALUES ('{$key}', '{$escapedVal}')");
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Pengaturan berhasil diperbarui!'
]);
