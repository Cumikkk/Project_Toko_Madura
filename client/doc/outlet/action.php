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
        $kecamatan = trim($db->real_escape_string($_POST['kecamatan'] ?? ''));
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

        // Fetch subscription fee from investor profile
        $nominalBiaya = 100000.00;
        $resFee = $db->query("SELECT biaya_langganan_outlet FROM investor WHERE id_investor = {$investorId} LIMIT 1");
        if ($resFee && $resFee->num_rows > 0) {
            $rowFee = $resFee->fetch_assoc();
            if (!empty($rowFee['biaya_langganan_outlet']) && (float)$rowFee['biaya_langganan_outlet'] > 0) {
                $nominalBiaya = (float)$rowFee['biaya_langganan_outlet'];
            }
        }

        $namaPengelola = trim($db->real_escape_string($_POST['nama_pengelola'] ?? ''));
        if (empty($namaPengelola)) {
            $namaPengelola = $namaOutlet;
        }
        $noHp = trim($db->real_escape_string($_POST['no_hp'] ?? ''));
        $persentasePotongan = isset($_POST['persentase_potongan']) ? (float)$_POST['persentase_potongan'] : 10.00;
        $persenBagianInvestor = isset($_POST['persen_bagian_investor']) ? (float)$_POST['persen_bagian_investor'] : 50.00;

        // Handle Upload Bukti Pembayaran
        $buktiPath = '';
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/bukti_pembayaran/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (!in_array($fileExt, $allowedExts)) {
                JsonResponse(['success' => false, 'message' => 'Format file bukti bayar tidak didukung. Harap unggah JPG, PNG, atau PDF.']);
            }

            $newFileName = 'bukti_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $targetFilePath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $targetFilePath)) {
                $buktiPath = 'uploads/bukti_pembayaran/' . $newFileName;
            }
        }

        if (empty($buktiPath)) {
            JsonResponse(['success' => false, 'message' => 'Harap unggah foto / file bukti transfer pembayaran pendaftaran outlet.']);
        }

        // Hash Password & Insert User Account
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $escapedHash = $db->real_escape_string($hashedPassword);

        $sqlUser = "INSERT INTO users (nama_lengkap, username, no_hp, password, role) VALUES ('{$namaPengelola}', '{$username}', '{$noHp}', '{$escapedHash}', 'outlet')";
        if (!$db->query($sqlUser)) {
            JsonResponse(['success' => false, 'message' => 'Gagal membuat akun user outlet: ' . $db->error]);
        }
        $newUserId = $db->insert_id;

        // Insert Outlet Record with status 'pending'
        $escapedBukti = $db->real_escape_string($buktiPath);
        $sqlOutlet = "INSERT INTO outlet (id_users, id_investor, persentase_potongan, persen_bagian_investor, nama_outlet, alamat_outlet, kecamatan, status, nominal_biaya, bukti_pembayaran, tanggal_request, tanggal_bergabung) VALUES ({$newUserId}, {$investorId}, {$persentasePotongan}, {$persenBagianInvestor}, '{$namaOutlet}', '{$alamatOutlet}', '{$kecamatan}', 'pending', {$nominalBiaya}, '{$escapedBukti}', NOW(), NOW())";
        if (!$db->query($sqlOutlet)) {
            JsonResponse(['success' => false, 'message' => 'Gagal menyimpan data outlet: ' . $db->error]);
        }

        JsonResponse([
            'success' => true,
            'message' => 'Request pendaftaran outlet "' . htmlspecialchars($namaOutlet) . '" & bukti transfer berhasil dikirim! Menunggu konfirmasi verifikasi dari Admin.'
        ]);
    }

    // =========================================================================
    // ACTION: REQUEST PERPANJANGAN LANGGANAN OUTLET (RENEWAL)
    // =========================================================================
    if ($action === 'request_perpanjangan' || $action === 'ajukan_ulang') {
        $idOutlet = (int)($_POST['id_outlet'] ?? 0);
        
        // Verify ownership
        $resCheck = $db->query("SELECT id_outlet, nama_outlet, bukti_pembayaran, tipe_request FROM outlet WHERE id_outlet = {$idOutlet} AND id_investor = {$investorId} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Outlet tidak ditemukan atau Anda tidak memiliki akses.']);
        }
        $rowOutlet = $resCheck->fetch_assoc();
        $namaOutlet = $rowOutlet['nama_outlet'];
        $tipeReqNow = ($action === 'request_perpanjangan') ? 'perpanjangan' : ($rowOutlet['tipe_request'] ?: 'baru');

        // Get subscription fee from investor profile
        $nominalBiaya = 100000.00;
        $resFee = $db->query("SELECT biaya_langganan_outlet FROM investor WHERE id_investor = {$investorId} LIMIT 1");
        if ($resFee && $resFee->num_rows > 0) {
            $rowFee = $resFee->fetch_assoc();
            if (!empty($rowFee['biaya_langganan_outlet']) && (float)$rowFee['biaya_langganan_outlet'] > 0) {
                $nominalBiaya = (float)$rowFee['biaya_langganan_outlet'];
            }
        }

        // Handle Upload Bukti Pembayaran
        $buktiPath = '';
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/bukti_pembayaran/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (!in_array($fileExt, $allowedExts)) {
                JsonResponse(['success' => false, 'message' => 'Format file bukti bayar tidak didukung. Harap unggah JPG, PNG, atau PDF.']);
            }

            $prefix = ($tipeReqNow === 'perpanjangan') ? 'bukti_renew_' : 'bukti_reapp_';
            $newFileName = $prefix . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $targetFilePath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $targetFilePath)) {
                $buktiPath = 'uploads/bukti_pembayaran/' . $newFileName;
            }
        }

        if (empty($buktiPath)) {
            JsonResponse(['success' => false, 'message' => 'Harap unggah foto / file bukti transfer pembayaran baru.']);
        }

        // Unlink old payment proof if exists
        $oldBukti = trim($rowOutlet['bukti_pembayaran'] ?? '');
        if (!empty($oldBukti)) {
            $path1 = __DIR__ . '/../../' . $oldBukti;
            if (file_exists($path1)) {
                @unlink($path1);
            }
        }

        $escapedBukti = $db->real_escape_string($buktiPath);

        // Update outlet status to pending
        $sqlUpdate = "UPDATE outlet SET 
                        status = 'pending',
                        tipe_request = '{$tipeReqNow}',
                        nominal_biaya = {$nominalBiaya},
                        bukti_pembayaran = '{$escapedBukti}',
                        tanggal_request = NOW()
                      WHERE id_outlet = {$idOutlet} AND id_investor = {$investorId}";

        if (!$db->query($sqlUpdate)) {
            JsonResponse(['success' => false, 'message' => 'Gagal mengajukan request: ' . $db->error]);
        }

        $msgText = ($tipeReqNow === 'perpanjangan')
            ? 'Request perpanjangan langganan untuk outlet "' . htmlspecialchars($namaOutlet) . '" & bukti transfer berhasil dikirim! Menunggu konfirmasi verifikasi dari Admin.'
            : 'Pengajuan ulang pendaftaran outlet "' . htmlspecialchars($namaOutlet) . '" & bukti transfer berhasil dikirim! Menunggu konfirmasi verifikasi dari Admin.';

        JsonResponse([
            'success' => true,
            'message' => $msgText
        ]);
    }

    // =========================================================================
    // ACTION: GET DETAIL FOR VIEW / EDIT
    // =========================================================================
    if ($action === 'get_detail') {
        $idOutlet = (int)($_GET['id_outlet'] ?? 0);
        $resDetail = $db->query("SELECT o.*, u.username, u.nama_lengkap, u.no_hp FROM outlet o JOIN users u ON o.id_users = u.id_users WHERE o.id_outlet = {$idOutlet} AND o.id_investor = {$investorId} LIMIT 1");
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
        $namaOutlet = trim($_POST['nama_outlet'] ?? '');
        $alamatOutlet = trim($_POST['alamat_outlet'] ?? '');
        $kecamatan = trim($_POST['kecamatan'] ?? '');
        $persentasePotongan = (float)($_POST['persentase_potongan'] ?? 10.00);
        $persenBagianInvestor = (float)($_POST['persen_bagian_investor'] ?? 50.00);
        $namaPengelola = trim($_POST['nama_pengelola'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($idOutlet) || empty($namaOutlet) || empty($kecamatan) || empty($alamatOutlet) || empty($namaPengelola) || empty($noHp) || empty($username)) {
            JsonResponse(['success' => false, 'message' => 'Mohon lengkapi semua kolom wajib (Nama Outlet, Kecamatan, Alamat, Nama Pengelola, No HP, Username).']);
        }

        // Verify ownership
        $resCheck = $db->query("SELECT id_users FROM outlet WHERE id_outlet = {$idOutlet} AND id_investor = {$investorId} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Outlet tidak ditemukan atau Anda tidak memiliki akses.']);
        }
        $associatedUserId = (int)$resCheck->fetch_assoc()['id_users'];

        // Check if username used by another user
        $safeUsername = $db->real_escape_string($username);
        $chkUser = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$safeUsername}') AND id_users != {$associatedUserId} LIMIT 1");
        if ($chkUser && $chkUser->num_rows > 0) {
            JsonResponse(['success' => false, 'message' => 'Username "' . htmlspecialchars($username) . '" sudah digunakan oleh pengguna lain.']);
        }

        // Escape variables right before query execution
        $safeNamaOutlet = $db->real_escape_string($namaOutlet);
        $safeAlamatOutlet = $db->real_escape_string($alamatOutlet);
        $safeKecamatan = $db->real_escape_string($kecamatan);

        // Check if custom date range scheme is requested
        $applyDateRange = isset($_POST['apply_date_range']) && (int)$_POST['apply_date_range'] === 1;
        $tglMulaiSkema = trim($_POST['tgl_mulai_skema'] ?? '');
        $tglSelesaiSkema = trim($_POST['tgl_selesai_skema'] ?? '');
        $affectedRowsOmzet = 0;

        if ($applyDateRange && !empty($tglMulaiSkema) && !empty($tglSelesaiSkema)) {
            if ($tglMulaiSkema > $tglSelesaiSkema) {
                JsonResponse(['success' => false, 'message' => 'Tanggal mulai skema tidak boleh lebih besar dari tanggal selesai.']);
            }
            $safeMulai = $db->real_escape_string($tglMulaiSkema);
            $safeSelesai = $db->real_escape_string($tglSelesaiSkema);

            // Update basic info on outlet table WITHOUT overwriting global default rates
            $updateOutlet = $db->query("UPDATE outlet SET 
                nama_outlet = '{$safeNamaOutlet}', 
                alamat_outlet = '{$safeAlamatOutlet}', 
                kecamatan = '{$safeKecamatan}' 
                WHERE id_outlet = {$idOutlet}");

            if (!$updateOutlet) {
                JsonResponse(['success' => false, 'message' => 'Gagal mengupdate data outlet: ' . $db->error]);
            }
            
            // Update existing laporan_omzet records within date range ONLY
            $db->query("UPDATE laporan_omzet SET 
                presentase_potongan = {$persentasePotongan},
                persen_bagian_investor = {$persenBagianInvestor},
                nominal_potongan = ROUND(omzet * ({$persentasePotongan} / 100.0), 2)
                WHERE id_outlet = {$idOutlet} AND periode_laporan BETWEEN '{$safeMulai}' AND '{$safeSelesai}'");
                
            $affectedRowsOmzet = $db->affected_rows;
        } else {
            // No date range specified: Update global default rates on outlet table
            $updateOutlet = $db->query("UPDATE outlet SET 
                nama_outlet = '{$safeNamaOutlet}', 
                alamat_outlet = '{$safeAlamatOutlet}', 
                kecamatan = '{$safeKecamatan}', 
                persentase_potongan = {$persentasePotongan}, 
                persen_bagian_investor = {$persenBagianInvestor} 
                WHERE id_outlet = {$idOutlet}");

            if (!$updateOutlet) {
                JsonResponse(['success' => false, 'message' => 'Gagal mengupdate data outlet: ' . $db->error]);
            }
        }

        // Update User Account
        $safeNamaPengelola = $db->real_escape_string($namaPengelola);
        $safeNoHp = $db->real_escape_string($noHp);
        
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $escapedHash = $db->real_escape_string($hashedPassword);
            $db->query("UPDATE users SET 
                nama_lengkap = '{$safeNamaPengelola}', 
                no_hp = '{$safeNoHp}', 
                username = '{$safeUsername}', 
                password = '{$escapedHash}' 
                WHERE id_users = {$associatedUserId}");
        } else {
            $db->query("UPDATE users SET 
                nama_lengkap = '{$safeNamaPengelola}', 
                no_hp = '{$safeNoHp}', 
                username = '{$safeUsername}' 
                WHERE id_users = {$associatedUserId}");
        }

        $successMsg = 'Data outlet berhasil diperbarui!';
        if ($affectedRowsOmzet > 0) {
            $tglStartFmt = date('d/m/Y', strtotime($tglMulaiSkema));
            $tglEndFmt = date('d/m/Y', strtotime($tglSelesaiSkema));
            $persenOutletVal = 100.00 - $persenBagianInvestor;
            $successMsg .= " Skema potongan {$persentasePotongan}% & bagi hasil (Investor {$persenBagianInvestor}% : Outlet {$persenOutletVal}%) berhasil diterapkan pada {$affectedRowsOmzet} data laporan omzet harian ({$tglStartFmt} s/d {$tglEndFmt}).";
        }

        JsonResponse(['success' => true, 'message' => $successMsg]);
    }

    // =========================================================================
    // ACTION: DELETE OUTLET
    // =========================================================================
    if ($action === 'delete') {
        $idOutlet = (int)($_POST['id_outlet'] ?? 0);

        // Verify ownership
        $resCheck = $db->query("SELECT id_users, nama_outlet, bukti_pembayaran FROM outlet WHERE id_outlet = {$idOutlet} AND id_investor = {$investorId} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Outlet tidak ditemukan atau Anda tidak memiliki akses.']);
        }
        $row = $resCheck->fetch_assoc();
        $associatedUserId = (int)$row['id_users'];
        $namaOutlet = $row['nama_outlet'];
        $buktiPembayaran = trim($row['bukti_pembayaran'] ?? '');

        // Delete associated omzet reports first
        $db->query("DELETE FROM laporan_omzet WHERE id_outlet = {$idOutlet}");

        // Delete from outlet table
        $db->query("DELETE FROM outlet WHERE id_outlet = {$idOutlet}");

        // Delete from users table
        $db->query("DELETE FROM users WHERE id_users = {$associatedUserId}");

        // Delete physical proof of payment file from server if exists
        if (!empty($buktiPembayaran)) {
            $path1 = WEB_ROOT . '/' . $buktiPembayaran;
            $path2 = CRM_ROOT . '/' . $buktiPembayaran;
            if (file_exists($path1)) {
                @unlink($path1);
            }
            if (file_exists($path2)) {
                @unlink($path2);
            }
        }

        JsonResponse(['success' => true, 'message' => 'Outlet "' . htmlspecialchars($namaOutlet) . '" berhasil dihapus!']);
    }

    JsonResponse(['success' => false, 'message' => 'Aksi tidak valid.']);

} catch (Exception $e) {
    JsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
