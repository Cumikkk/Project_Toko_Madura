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

    $resBkt = $db->query("SELECT bukti_pembayaran FROM komisi_master WHERE id_komisi = {$idKomisi} LIMIT 1");
    if ($resBkt && $rowBkt = $resBkt->fetch_assoc()) {
        $oldFile = trim($rowBkt['bukti_pembayaran'] ?? '');
        if (!empty($oldFile)) {
            @unlink(CRM_ROOT . '/' . $oldFile);
            @unlink(WEB_ROOT . '/' . $oldFile);
        }
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

$buktiPath = null;
if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['bukti_pembayaran']['tmp_name'];
    $fileName    = $_FILES['bukti_pembayaran']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    if (in_array($fileExtension, $allowedExtensions)) {
        $targetDirAdmin = CRM_ROOT . '/uploads/bukti_komisi/';
        $targetDirClient = WEB_ROOT . '/uploads/bukti_komisi/';
        if (!is_dir($targetDirAdmin)) {
            @mkdir($targetDirAdmin, 0777, true);
        }
        if (!is_dir($targetDirClient)) {
            @mkdir($targetDirClient, 0777, true);
        }

        $newFileName = 'bukti_komisi_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
        $destPathAdmin = $targetDirAdmin . $newFileName;
        $destPathClient = $targetDirClient . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPathAdmin)) {
            @copy($destPathAdmin, $destPathClient);
            $buktiPath = 'uploads/bukti_komisi/' . $newFileName;
        }
    }
}

$periodeEsc = $db->real_escape_string($periode);
$catatanEsc = $db->real_escape_string($catatan);

if ($idKomisi > 0) {
    // UPDATE
    $updateBukti = "";
    if (!empty($buktiPath)) {
        // Hapus berkas bukti lama dari disk jika ada berkas baru diunggah
        $resBkt = $db->query("SELECT bukti_pembayaran FROM komisi_master WHERE id_komisi = {$idKomisi} LIMIT 1");
        if ($resBkt && $rowBkt = $resBkt->fetch_assoc()) {
            $oldFile = trim($rowBkt['bukti_pembayaran'] ?? '');
            if (!empty($oldFile) && $oldFile !== $buktiPath) {
                @unlink(CRM_ROOT . '/' . $oldFile);
                @unlink(WEB_ROOT . '/' . $oldFile);
            }
        }

        $buktiEsc = $db->real_escape_string($buktiPath);
        $updateBukti = ", bukti_pembayaran = '{$buktiEsc}'";
    }
    $sql = "UPDATE komisi_master 
            SET id_master = {$idMaster}, 
                tanggal_komisi = '{$tanggal}', 
                periode = '{$periodeEsc}', 
                nominal = {$nominal}, 
                catatan = '{$catatanEsc}'
                {$updateBukti}
            WHERE id_komisi = {$idKomisi}";
    $db->query($sql);
    echo json_encode(['success' => true, 'message' => 'Data komisi master berhasil diperbarui.']);
} else {
    // INSERT
    $buktiVal = !empty($buktiPath) ? "'" . $db->real_escape_string($buktiPath) . "'" : "NULL";
    $sql = "INSERT INTO komisi_master (id_master, tanggal_komisi, periode, nominal, catatan, bukti_pembayaran, created_at) 
            VALUES ({$idMaster}, '{$tanggal}', '{$periodeEsc}', {$nominal}, '{$catatanEsc}', {$buktiVal}, NOW())";
    $db->query($sql);
    echo json_encode(['success' => true, 'message' => 'Data komisi master berhasil ditambahkan.']);
}
