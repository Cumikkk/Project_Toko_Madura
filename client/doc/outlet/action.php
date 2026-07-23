<?php
require_once __DIR__ . "/../../../config/setting.php";

header('Content-Type: application/json');

use Config\Core\Database;
use App\Models\User;

try {
    // 1. Session Auth Check
    $user = User::user();
    if (!$user) {
        JsonResponse(['success' => false, 'message' => 'Sesi login telah berakhir. Silakan login kembali.']);
    }

    $db = Database::connect();
    if (!$db) {
        JsonResponse(['success' => false, 'message' => 'Gagal terhubung ke database.']);
    }

    $userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);
    if ($userId <= 0) {
        JsonResponse(['success' => false, 'message' => 'User ID tidak valid.']);
    }

    // 2. Get Investor Record for Logged-In User
    $resInv = $db->query("SELECT id_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
    if ($resInv && $resInv->num_rows > 0) {
        $investorId = (int)$resInv->fetch_assoc()['id_investor'];
    } else {
        // Auto-create investor record if not present
        $db->query("INSERT INTO investor (id_users, id_master, alamat_investor, persen_bagian_investor) VALUES ({$userId}, 1, 'Bangkalan', 50.00)");
        $investorId = $db->insert_id;
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // =========================================================================
    // ACTION: CREATE / ADD OUTLET
    // =========================================================================
    if ($action === 'add') {
        $namaOutlet = trim($db->real_escape_string($_POST['nama_outlet'] ?? ''));
        $alamatOutlet = trim($db->real_escape_string($_POST['alamat_outlet'] ?? ''));
        $username = trim($db->real_escape_string($_POST['username'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if (empty($namaOutlet) || empty($username) || empty($password)) {
            JsonResponse(['success' => false, 'message' => 'Mohon lengkapi Nama Outlet, Username, dan Password.']);
        }

        // Check if username already exists in users table
        $chkUser = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$username}') LIMIT 1");
        if ($chkUser && $chkUser->num_rows > 0) {
            JsonResponse(['success' => false, 'message' => 'Username "' . htmlspecialchars($username) . '" sudah digunakan. Silakan gunakan username lain.']);
        }

        // Auto-generate Kode Outlet (OUT001, OUT002, ...)
        $resCode = $db->query("SELECT MAX(id_outlet) as max_id FROM outlet");
        $maxId = 0;
        if ($resCode && $rowCode = $resCode->fetch_assoc()) {
            $maxId = (int)$rowCode['max_id'];
        }
        $kodeOutlet = 'OUT' . sprintf('%03d', $maxId + 1);

        // Hash Password & Insert User Account
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $escapedHash = $db->real_escape_string($hashedPassword);

        $sqlUser = "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('{$namaOutlet}', '{$username}', '{$escapedHash}', 'outlet')";
        if (!$db->query($sqlUser)) {
            JsonResponse(['success' => false, 'message' => 'Gagal membuat akun user outlet: ' . $db->error]);
        }
        $newUserId = $db->insert_id;

        // Insert Outlet Record
        $sqlOutlet = "INSERT INTO outlet (id_users, id_investor, kode_outlet, nama_outlet, alamat_outlet) VALUES ({$newUserId}, {$investorId}, '{$kodeOutlet}', '{$namaOutlet}', '{$alamatOutlet}')";
        if (!$db->query($sqlOutlet)) {
            JsonResponse(['success' => false, 'message' => 'Gagal menyimpan data outlet: ' . $db->error]);
        }

        JsonResponse([
            'success' => true,
            'message' => 'Outlet "' . htmlspecialchars($namaOutlet) . '" (' . $kodeOutlet . ') berhasil didaftarkan!'
        ]);
    }

    // =========================================================================
    // ACTION: GET DETAIL FOR VIEW / EDIT
    // =========================================================================
    if ($action === 'get_detail') {
        $idOutlet = (int)($_GET['id_outlet'] ?? 0);
        $resDetail = $db->query("SELECT o.*, u.username, u.nama_lengkap, u.email FROM outlet o JOIN users u ON o.id_users = u.id_users WHERE o.id_outlet = {$idOutlet} AND o.id_investor = {$investorId} LIMIT 1");
        if (!$resDetail || $resDetail->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Data outlet tidak ditemukan.']);
        }

        $detail = $resDetail->fetch_assoc();
        
        // Calculate omzet statistics for this outlet
        $resOmzet = $db->query("SELECT COUNT(*) as total_laporan, IFNULL(SUM(omzet), 0) as total_omzet, IFNULL(SUM(nominal_potongan), 0) as total_potongan FROM laporan_omzet WHERE id_outlet = {$idOutlet}");
        $statOmzet = $resOmzet ? $resOmzet->fetch_assoc() : ['total_laporan' => 0, 'total_omzet' => 0, 'total_potongan' => 0];

        $detail['total_laporan'] = (int)$statOmzet['total_laporan'];
        $detail['total_omzet'] = (float)$statOmzet['total_omzet'];
        $detail['total_potongan'] = (float)$statOmzet['total_potongan'];

        JsonResponse(['success' => true, 'data' => $detail]);
    }

    // =========================================================================
    // ACTION: EDIT / UPDATE OUTLET
    // =========================================================================
    if ($action === 'edit') {
        $idOutlet = (int)($_POST['id_outlet'] ?? 0);
        $namaOutlet = trim($db->real_escape_string($_POST['nama_outlet'] ?? ''));
        $alamatOutlet = trim($db->real_escape_string($_POST['alamat_outlet'] ?? ''));
        $username = trim($db->real_escape_string($_POST['username'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if (empty($idOutlet) || empty($namaOutlet) || empty($username)) {
            JsonResponse(['success' => false, 'message' => 'Mohon lengkapi Nama Outlet dan Username.']);
        }

        // Verify ownership
        $resCheck = $db->query("SELECT id_users FROM outlet WHERE id_outlet = {$idOutlet} AND id_investor = {$investorId} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Outlet tidak ditemukan atau Anda tidak memiliki akses.']);
        }
        $associatedUserId = (int)$resCheck->fetch_assoc()['id_users'];

        // Check if username used by another user
        $chkUser = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$username}') AND id_users != {$associatedUserId} LIMIT 1");
        if ($chkUser && $chkUser->num_rows > 0) {
            JsonResponse(['success' => false, 'message' => 'Username "' . htmlspecialchars($username) . '" sudah digunakan oleh pengguna lain.']);
        }

        // Update Outlet
        $db->query("UPDATE outlet SET nama_outlet = '{$namaOutlet}', alamat_outlet = '{$alamatOutlet}' WHERE id_outlet = {$idOutlet}");

        // Update User Account
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $escapedHash = $db->real_escape_string($hashedPassword);
            $db->query("UPDATE users SET nama_lengkap = '{$namaOutlet}', username = '{$username}', password = '{$escapedHash}' WHERE id_users = {$associatedUserId}");
        } else {
            $db->query("UPDATE users SET nama_lengkap = '{$namaOutlet}', username = '{$username}' WHERE id_users = {$associatedUserId}");
        }

        JsonResponse(['success' => true, 'message' => 'Data Outlet berhasil diperbarui!']);
    }

    // =========================================================================
    // ACTION: DELETE OUTLET
    // =========================================================================
    if ($action === 'delete') {
        $idOutlet = (int)($_POST['id_outlet'] ?? 0);

        // Verify ownership
        $resCheck = $db->query("SELECT id_users, nama_outlet FROM outlet WHERE id_outlet = {$idOutlet} AND id_investor = {$investorId} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Outlet tidak ditemukan atau Anda tidak memiliki akses.']);
        }
        $row = $resCheck->fetch_assoc();
        $associatedUserId = (int)$row['id_users'];
        $namaOutlet = $row['nama_outlet'];

        // Delete associated omzet reports first
        $db->query("DELETE FROM laporan_omzet WHERE id_outlet = {$idOutlet}");

        // Delete from outlet table
        $db->query("DELETE FROM outlet WHERE id_outlet = {$idOutlet}");

        // Delete from users table
        $db->query("DELETE FROM users WHERE id_users = {$associatedUserId}");

        JsonResponse(['success' => true, 'message' => 'Outlet "' . htmlspecialchars($namaOutlet) . '" berhasil dihapus!']);
    }

    JsonResponse(['success' => false, 'message' => 'Aksi tidak valid.']);

} catch (Exception $e) {
    JsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
