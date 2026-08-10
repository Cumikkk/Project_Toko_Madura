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

    // 2. Get Outlet Record for Logged-In User
    $resOutlet = $db->query("SELECT o.*, u_inv.alamat as alamat_investor FROM outlet o LEFT JOIN investor i ON o.id_investor = i.id_investor LEFT JOIN users u_inv ON u_inv.id_users = i.id_users WHERE o.id_users = {$userId} LIMIT 1");
    if (!$resOutlet || $resOutlet->num_rows === 0) {
        JsonResponse(['success' => false, 'message' => 'Akun Anda belum terhubung dengan data outlet. Mohon hubungi Investor Anda.']);
    }
    $outlet = $resOutlet->fetch_assoc();
    $idOutlet = (int)$outlet['id_outlet'];

    // 3. Fetch Discount Percentage directly from Outlet record (set during registration)
    $presentaseGlobal = isset($outlet['persentase_potongan']) ? (float)$outlet['persentase_potongan'] : 10.00;

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Array nama bulan Bahasa Indonesia
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];



    // =========================================================================
    // ACTION: INPUT OMZET HARIAN (ADD LAPORAN OMZET)
    // =========================================================================
    if ($action === 'add') {
        $tanggalOmzet = trim($_POST['periode_laporan'] ?? $_POST['tanggal_omzet'] ?? date('Y-m-d'));
        $rawOmzet = str_replace(['.', ',', 'Rp', ' '], '', $_POST['omzet'] ?? '0');
        $omzet = (float)$rawOmzet;

        if (empty($tanggalOmzet)) {
            JsonResponse(['success' => false, 'message' => 'Mohon pilih tanggal penginputan omzet.']);
        }

        if ($omzet <= 0) {
            JsonResponse(['success' => false, 'message' => 'Nominal omzet harian harus lebih besar dari Rp 0.']);
        }

        $escapedTanggal = $db->real_escape_string($tanggalOmzet);
        $timeVal = strtotime($tanggalOmzet);
        $entryYM = date('Y-m', $timeVal);
        $lastDayOfMonth = date('Y-m-t', $timeVal); // e.g. 2026-08-31
        
        $namaBulanTahun = ($bulanIndo[(int)date('n', $timeVal)] ?? '') . ' ' . date('Y', $timeVal);
        $tglStr = date('d/m/Y', $timeVal);
        $tglLastDayStr = date('d/m/Y', strtotime($lastDayOfMonth));

        // Check duplicate for exact same date
        $chkDup = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$escapedTanggal}' LIMIT 1");
        if ($chkDup && $chkDup->num_rows > 0) {
            JsonResponse(['success' => false, 'message' => 'Omzet harian tanggal ' . $tglStr . ' sudah pernah diinput. Silakan gunakan tombol edit jika ingin mengubah.']);
        }

        // Smart Rate Resolution: Determine percentage rate for $tanggalOmzet
        // 1. Check if a rate was already set in laporan_omzet for this exact date
        $chkRate = $db->query("SELECT presentase_potongan, persen_bagian_investor FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$escapedTanggal}' AND presentase_potongan > 0 LIMIT 1");
        
        if ($chkRate && $chkRate->num_rows > 0) {
            $rRate = $chkRate->fetch_assoc();
            $appliedPercent = (float)$rRate['presentase_potongan'];
            $persenInvGlobal = (float)$rRate['persen_bagian_investor'];
        } else {
            // 2. Check nearest preceding entry in laporan_omzet before $tanggalOmzet
            $chkPrev = $db->query("SELECT presentase_potongan, persen_bagian_investor FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan < '{$escapedTanggal}' AND presentase_potongan > 0 ORDER BY periode_laporan DESC LIMIT 1");
            
            if ($chkPrev && $chkPrev->num_rows > 0) {
                $rPrev = $chkPrev->fetch_assoc();
                $appliedPercent = (float)$rPrev['presentase_potongan'];
                $persenInvGlobal = (float)$rPrev['persen_bagian_investor'];
            } else {
                // 3. Fallback to default outlet table rates
                $appliedPercent = isset($outlet['persentase_potongan']) ? (float)$outlet['persentase_potongan'] : 10.00;
                $persenInvGlobal = isset($outlet['persen_bagian_investor']) ? (float)$outlet['persen_bagian_investor'] : 50.00;
            }
        }

        $nominalPotongan = round($omzet * ($appliedPercent / 100.0), 2);

        $waktuInput = date('Y-m-d H:i:s');

        $sqlInsert = "INSERT INTO laporan_omzet (id_outlet, periode_laporan, omzet, presentase_potongan, persen_bagian_investor, nominal_potongan, waktu_input) VALUES ({$idOutlet}, '{$escapedTanggal}', {$omzet}, {$appliedPercent}, {$persenInvGlobal}, {$nominalPotongan}, '{$waktuInput}')";
        
        if (!$db->query($sqlInsert)) {
            JsonResponse(['success' => false, 'message' => 'Gagal menyimpan omzet harian: ' . $db->error]);
        }

        $bersihOutlet = $omzet - $nominalPotongan;

        JsonResponse([
            'success' => true,
            'message' => 'Omzet harian tanggal ' . $tglStr . ' sebesar Rp ' . number_format($omzet, 0, ',', '.') . ' berhasil disimpan!',
            'data' => [
                'omzet' => $omzet,
                'omzet_formatted' => 'Rp ' . number_format($omzet, 0, ',', '.'),
                'tgl_formatted' => $tglStr,
                'waktu_input' => date('d/m/Y H:i', strtotime($waktuInput)),
                'is_last_day' => ($tanggalOmzet === $lastDayOfMonth),
                'potongan' => $nominalPotongan,
                'bersih' => $bersihOutlet,
                'presentase' => $appliedPercent,
                'persentase_potongan' => number_format($presentaseGlobal, 0),
                'periode_str' => $namaBulanTahun
            ]
        ]);
    }

    // =========================================================================
    // ACTION: GET DETAIL FOR VIEW / EDIT
    // =========================================================================
    if ($action === 'get_detail') {
        $idLaporan = (int)($_GET['id_laporan'] ?? 0);
        $resDetail = $db->query("SELECT * FROM laporan_omzet WHERE id_laporan = {$idLaporan} AND id_outlet = {$idOutlet} LIMIT 1");
        if (!$resDetail || $resDetail->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Data laporan omzet tidak ditemukan.']);
        }

        $detail = $resDetail->fetch_assoc();
        $detail['bersih_outlet'] = (float)$detail['omzet'] - (float)$detail['nominal_potongan'];
        
        $timestamp = strtotime($detail['periode_laporan']);
        $detail['tgl_formatted'] = date('Y-m-d', $timestamp);
        $detail['tgl_indo'] = date('d/m/Y', $timestamp);
        $detail['is_last_day'] = (date('Y-m-d', $timestamp) === date('Y-m-t', $timestamp));

        JsonResponse(['success' => true, 'data' => $detail]);
    }

    // =========================================================================
    // ACTION: EDIT / UPDATE LAPORAN OMZET HARIAN
    // =========================================================================
    if ($action === 'edit') {
        $idLaporan = (int)($_POST['id_laporan'] ?? 0);
        $tanggalOmzet = trim($_POST['tanggal_omzet'] ?? '');
        $rawOmzet = str_replace(['.', ',', 'Rp', ' '], '', $_POST['omzet'] ?? '0');
        $omzet = (float)$rawOmzet;

        if (empty($idLaporan) || empty($tanggalOmzet)) {
            JsonResponse(['success' => false, 'message' => 'Mohon lengkapi tanggal dan nominal omzet.']);
        }

        if ($omzet <= 0) {
            JsonResponse(['success' => false, 'message' => 'Nominal omzet harian harus lebih besar dari Rp 0.']);
        }

        // Verify ownership
        $resCheck = $db->query("SELECT id_laporan, presentase_potongan FROM laporan_omzet WHERE id_laporan = {$idLaporan} AND id_outlet = {$idOutlet} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Data omzet tidak ditemukan atau Anda tidak memiliki akses.']);
        }

        $escapedTanggal = $db->real_escape_string($tanggalOmzet);
        $tglStr = date('d/m/Y', strtotime($tanggalOmzet));

        $rowCheck = $resCheck->fetch_assoc();
        $appliedPercent = (isset($rowCheck['presentase_potongan']) && (float)$rowCheck['presentase_potongan'] > 0) ? (float)$rowCheck['presentase_potongan'] : $presentaseGlobal;
        $nominalPotongan = round($omzet * ($appliedPercent / 100.0), 2);

        $sqlUpdate = "UPDATE laporan_omzet SET periode_laporan = '{$escapedTanggal}', omzet = {$omzet}, presentase_potongan = {$appliedPercent}, nominal_potongan = {$nominalPotongan} WHERE id_laporan = {$idLaporan} AND id_outlet = {$idOutlet}";
        if (!$db->query($sqlUpdate)) {
            JsonResponse(['success' => false, 'message' => 'Gagal memperbarui omzet harian: ' . $db->error]);
        }

        JsonResponse(['success' => true, 'message' => 'Omzet harian tanggal ' . $tglStr . ' berhasil diperbarui!']);
    }

    // =========================================================================
    // ACTION: DELETE SINGLE LAPORAN OMZET
    // =========================================================================
    if ($action === 'delete') {
        $idLaporan = (int)($_POST['id_laporan'] ?? 0);

        // Verify ownership
        $resCheck = $db->query("SELECT id_laporan, periode_laporan FROM laporan_omzet WHERE id_laporan = {$idLaporan} AND id_outlet = {$idOutlet} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Data omzet tidak ditemukan atau Anda tidak memiliki akses.']);
        }

        $row = $resCheck->fetch_assoc();
        $tglStr = date('d/m/Y', strtotime($row['periode_laporan']));

        $db->query("DELETE FROM laporan_omzet WHERE id_laporan = {$idLaporan} AND id_outlet = {$idOutlet}");

        JsonResponse(['success' => true, 'message' => 'Omzet harian tanggal ' . $tglStr . ' berhasil dihapus!']);
    }

    // =========================================================================
    // ACTION: DELETE BULK / TERPILIH LAPORAN OMZET
    // =========================================================================
    if ($action === 'delete_selected' || $action === 'delete_bulk') {
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            JsonResponse(['success' => false, 'message' => 'Mohon pilih sekurang-kurangnya satu data omzet yang ingin dihapus.']);
        }

        $cleanIds = array_map('intval', $ids);
        $cleanIds = array_filter($cleanIds, fn($id) => $id > 0);

        if (empty($cleanIds)) {
            JsonResponse(['success' => false, 'message' => 'Data ID yang dipilih tidak valid.']);
        }

        $idListStr = implode(',', $cleanIds);

        // Verify ownership count
        $resCheck = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_laporan IN ({$idListStr}) AND id_outlet = {$idOutlet}");
        $validCount = $resCheck ? $resCheck->num_rows : 0;

        if ($validCount <= 0) {
            JsonResponse(['success' => false, 'message' => 'Data omzet tidak ditemukan atau Anda tidak memiliki akses.']);
        }

        // Delete selected records belonging to this outlet
        $db->query("DELETE FROM laporan_omzet WHERE id_laporan IN ({$idListStr}) AND id_outlet = {$idOutlet}");

        JsonResponse([
            'success' => true,
            'message' => 'Berhasil menghapus ' . $validCount . ' data omzet harian yang dipilih!'
        ]);
    }

    JsonResponse(['success' => false, 'message' => 'Aksi tidak valid.']);

} catch (Exception $e) {
    JsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
