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
    $resOutlet = $db->query("SELECT o.*, i.alamat_investor FROM outlet o LEFT JOIN investor i ON o.id_investor = i.id_investor WHERE o.id_users = {$userId} LIMIT 1");
    if (!$resOutlet || $resOutlet->num_rows === 0) {
        JsonResponse(['success' => false, 'message' => 'Akun Anda belum terhubung dengan data outlet. Mohon hubungi Investor Anda.']);
    }
    $outlet = $resOutlet->fetch_assoc();
    $idOutlet = (int)$outlet['id_outlet'];

    // 3. Fetch Global Discount Percentage from pengaturan_sistem
    $resGlobal = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'potongan_global' LIMIT 1");
    $presentaseGlobal = 10.00; // Default fallback 10%
    if ($resGlobal && $rowGlobal = $resGlobal->fetch_assoc()) {
        $presentaseGlobal = (float)$rowGlobal['nilai'];
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Array nama bulan Bahasa Indonesia
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    // Auto sync deduction percentage for months where last day is present
    $resAllMonths = $db->query("SELECT DISTINCT DATE_FORMAT(periode_laporan, '%Y-%m') as ym FROM laporan_omzet WHERE id_outlet = {$idOutlet}");
    if ($resAllMonths) {
        while ($mRow = $resAllMonths->fetch_assoc()) {
            $ym = $mRow['ym'];
            $lastDayStr = date('Y-m-t', strtotime($ym . '-01'));
            
            $chkLastDayInMonth = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$lastDayStr}' LIMIT 1");
            if ($chkLastDayInMonth && $chkLastDayInMonth->num_rows > 0) {
                // Last day is present -> 10% applied
                $db->query("UPDATE laporan_omzet SET presentase_potongan = {$presentaseGlobal}, nominal_potongan = ROUND(omzet * ({$presentaseGlobal} / 100), 2) WHERE id_outlet = {$idOutlet} AND DATE_FORMAT(periode_laporan, '%Y-%m') = '{$ym}'");
            } else {
                // Last day NOT present -> reset to 0%
                $db->query("UPDATE laporan_omzet SET presentase_potongan = 0.00, nominal_potongan = 0.00 WHERE id_outlet = {$idOutlet} AND DATE_FORMAT(periode_laporan, '%Y-%m') = '{$ym}'");
            }
        }
    }

    // =========================================================================
    // ACTION: INPUT OMZET HARIAN (ADD LAPORAN OMZET)
    // =========================================================================
    if ($action === 'add') {
        $tanggalOmzet = trim($_POST['tanggal_omzet'] ?? date('Y-m-d'));
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

        // Check if inputting ON the LAST DAY of the month OR if last day is already submitted
        $isLastDayInput = ($tanggalOmzet === $lastDayOfMonth);
        $chkLastDayInDb = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$lastDayOfMonth}' LIMIT 1");
        $isLastDayInDb = ($chkLastDayInDb && $chkLastDayInDb->num_rows > 0);

        if ($isLastDayInput || $isLastDayInDb) {
            $appliedPercent = $presentaseGlobal; // 10.00%
            $nominalPotongan = round($omzet * ($appliedPercent / 100), 2);
            $noteMsg = ' (Potongan 10% aktif diproses karena menginput di tanggal akhir bulan ' . $tglLastDayStr . ')';

            // Apply 10% deduction to all entries in this month
            $db->query("UPDATE laporan_omzet SET presentase_potongan = {$presentaseGlobal}, nominal_potongan = ROUND(omzet * ({$presentaseGlobal} / 100), 2) WHERE id_outlet = {$idOutlet} AND DATE_FORMAT(periode_laporan, '%Y-%m') = '{$entryYM}'");
        } else {
            // Normal day -> 0% deduction
            $appliedPercent = 0.00;
            $nominalPotongan = 0.00;
            $noteMsg = ' (Omzet utuh tanpa potongan)';
        }

        $waktuInput = date('Y-m-d H:i:s');

        $sqlInsert = "INSERT INTO laporan_omzet (id_outlet, periode_laporan, omzet, presentase_potongan, nominal_potongan, waktu_input) VALUES ({$idOutlet}, '{$escapedTanggal}', {$omzet}, {$appliedPercent}, {$nominalPotongan}, '{$waktuInput}')";
        
        if (!$db->query($sqlInsert)) {
            JsonResponse(['success' => false, 'message' => 'Gagal menyimpan omzet harian: ' . $db->error]);
        }

        $bersihOutlet = $omzet - $nominalPotongan;

        JsonResponse([
            'success' => true,
            'message' => 'Omzet harian tanggal ' . $tglStr . ' sebesar Rp ' . number_format($omzet, 0, ',', '.') . ' berhasil disimpan!' . $noteMsg,
            'data' => [
                'omzet' => $omzet,
                'potongan' => $nominalPotongan,
                'bersih' => $bersihOutlet,
                'presentase' => $appliedPercent,
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
        $resCheck = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_laporan = {$idLaporan} AND id_outlet = {$idOutlet} LIMIT 1");
        if (!$resCheck || $resCheck->num_rows === 0) {
            JsonResponse(['success' => false, 'message' => 'Data omzet tidak ditemukan atau Anda tidak memiliki akses.']);
        }

        $escapedTanggal = $db->real_escape_string($tanggalOmzet);
        $timeVal = strtotime($tanggalOmzet);
        $entryYM = date('Y-m', $timeVal);
        $lastDayOfMonth = date('Y-m-t', $timeVal);
        $tglStr = date('d/m/Y', $timeVal);

        // Check duplicate date for other reports
        $chkDup = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$escapedTanggal}' AND id_laporan != {$idLaporan} LIMIT 1");
        if ($chkDup && $chkDup->num_rows > 0) {
            JsonResponse(['success' => false, 'message' => 'Omzet harian untuk tanggal ' . $tglStr . ' sudah pernah diinput pada data lain.']);
        }

        $isLastDayInput = ($tanggalOmzet === $lastDayOfMonth);
        $chkLastDayInDb = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$lastDayOfMonth}' LIMIT 1");
        $isLastDayInDb = ($chkLastDayInDb && $chkLastDayInDb->num_rows > 0);

        if ($isLastDayInput || $isLastDayInDb) {
            $appliedPercent = $presentaseGlobal;
            $nominalPotongan = round($omzet * ($appliedPercent / 100), 2);
            $db->query("UPDATE laporan_omzet SET presentase_potongan = {$presentaseGlobal}, nominal_potongan = ROUND(omzet * ({$presentaseGlobal} / 100), 2) WHERE id_outlet = {$idOutlet} AND DATE_FORMAT(periode_laporan, '%Y-%m') = '{$entryYM}'");
        } else {
            $appliedPercent = 0.00;
            $nominalPotongan = 0.00;
        }

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
