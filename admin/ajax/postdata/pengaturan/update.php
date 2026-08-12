<?php
require_once __DIR__ . "/../../../../config/setting.php";
use App\Models\Pengaturan;

header('Content-Type: application/json');

$type = trim($_POST['setting_type'] ?? '');
$updates = [];

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

// Gunakan Model Pengaturan
$result = Pengaturan::updateSettings($updates);

echo json_encode($result);
