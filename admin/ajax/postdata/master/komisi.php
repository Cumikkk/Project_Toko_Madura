<?php
require_once __DIR__ . "/../../../../config/setting.php";
use Config\Core\Database;

header('Content-Type: application/json');
$db = Database::connect();

$action   = trim($_POST['action'] ?? '');
$idKomisi = intval($_POST['id_komisi'] ?? 0);

// DELETE ACTION
if ($action === 'delete') {
    if ($idKomisi <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID Komisi tidak valid.']);
        exit;
    }

    $db->query("DELETE FROM komisi_master WHERE id_komisi = {$idKomisi}");
    echo json_encode(['success' => true, 'message' => 'Data komisi master berhasil dihapus.']);
    exit;
}

// INSERT / UPDATE ACTION
$idMaster  = intval($_POST['id_master'] ?? 0);
$tanggal   = trim($_POST['tanggal_komisi'] ?? '');
$periode   = trim($_POST['periode'] ?? '');
$nominal   = (float)($_POST['nominal'] ?? 0);
$catatan   = trim($_POST['catatan'] ?? '');

if ($idMaster <= 0) {
    echo json_encode(['success' => false, 'message' => 'Harap pilih Master Owner!']);
    exit;
}

if (empty($tanggal)) {
    $tanggal = date('Y-m-d H:i:s');
} else {
    $tanggal = date('Y-m-d H:i:s', strtotime($tanggal));
}

if (empty($periode)) {
    echo json_encode(['success' => false, 'message' => 'Harap isi periode / keterangan komisi!']);
    exit;
}

if ($nominal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nominal komisi harus lebih besar dari Rp 0!']);
    exit;
}

$periodeEsc = $db->real_escape_string($periode);
$catatanEsc = $db->real_escape_string($catatan);

if ($idKomisi > 0) {
    // UPDATE
    $sql = "UPDATE komisi_master 
            SET id_master = {$idMaster}, 
                tanggal_komisi = '{$tanggal}', 
                periode = '{$periodeEsc}', 
                nominal = {$nominal}, 
                catatan = '{$catatanEsc}' 
            WHERE id_komisi = {$idKomisi}";
    $db->query($sql);
    echo json_encode(['success' => true, 'message' => 'Data komisi master berhasil diperbarui.']);
} else {
    // INSERT
    $sql = "INSERT INTO komisi_master (id_master, tanggal_komisi, periode, nominal, catatan, created_at) 
            VALUES ({$idMaster}, '{$tanggal}', '{$periodeEsc}', {$nominal}, '{$catatanEsc}', NOW())";
    $db->query($sql);
    echo json_encode(['success' => true, 'message' => 'Data komisi master berhasil ditambahkan.']);
}
