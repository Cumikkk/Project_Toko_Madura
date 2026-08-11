<?php
require_once __DIR__ . "/../../../config/setting.php";

header('Content-Type: application/json');

use Config\Core\Database;
use App\Models\User;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_detail_harian') {
    $idOutlet = isset($_GET['id_outlet']) ? (int)$_GET['id_outlet'] : 0;
    $tglMulai = isset($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
    $tglSelesai = isset($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
    $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
    $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

    if ($idOutlet <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID Outlet tidak valid.']);
        exit;
    }

    // Fetch outlet details and investor split percentage
    $resOut = $db->query("
        SELECT u.nama_lengkap as nama_outlet, o.persentase_potongan, o.persentase_hak_investor 
        FROM outlet o 
        JOIN users u ON u.id_users = o.id_users
        WHERE o.id_outlet = {$idOutlet} 
        LIMIT 1
    ");
    if (!$resOut || $resOut->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Outlet tidak ditemukan.']);
        exit;
    }
    $rowOutInfo = $resOut->fetch_assoc();
    $namaOutlet = $rowOutInfo['nama_outlet'];
    $ratePotongan = isset($rowOutInfo['persentase_potongan']) ? (float)$rowOutInfo['persentase_potongan'] : 10.00;
    $persenInvSplit = isset($rowOutInfo['persentase_hak_investor']) ? (float)$rowOutInfo['persentase_hak_investor'] : 50.00;
    $persenOutSplit = 100.00 - $persenInvSplit;

    // Build filter conditions based on passed parameters
    $whereConds = ["l.id_outlet = {$idOutlet}"];

    if (!empty($tglMulai) && !empty($tglSelesai)) {
        $safeMulai = $db->real_escape_string($tglMulai);
        $safeSelesai = $db->real_escape_string($tglSelesai);
        $whereConds[] = "l.tanggal_omzet BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
    } elseif (!empty($tglMulai)) {
        $safeMulai = $db->real_escape_string($tglMulai);
        $whereConds[] = "l.tanggal_omzet >= '{$safeMulai}'";
    } elseif (!empty($tglSelesai)) {
        $safeSelesai = $db->real_escape_string($tglSelesai);
        $whereConds[] = "l.tanggal_omzet <= '{$safeSelesai}'";
    } else {
        if ($bulan > 0) {
            $whereConds[] = "MONTH(l.tanggal_omzet) = {$bulan}";
        }
        if ($tahun > 0) {
            $whereConds[] = "YEAR(l.tanggal_omzet) = {$tahun}";
        }
    }

    $whereSql = implode(" AND ", $whereConds);
    $sql = "SELECT id_laporan, tanggal_omzet, nominal_omzet, persentase_potongan, persentase_hak_investor, nominal_potongan, created_at FROM laporan_omzet l WHERE {$whereSql} ORDER BY tanggal_omzet ASC";
    $res = $db->query($sql);

    $items = [];
    $totOmzet = 0;
    $totPotongan = 0;
    $totHakInvestor = 0;
    $totHakOutlet = 0;

    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $nominal_omzet = (float)$row['nominal_omzet'];
            $tglStr = date('d', strtotime($row['tanggal_omzet'])) . ' ' . 
                      ($bulanIndo[(int)date('n', strtotime($row['tanggal_omzet']))] ?? '') . ' ' . 
                      date('Y', strtotime($row['tanggal_omzet']));

            $itemRatePot = (isset($row['persentase_potongan']) && (float)$row['persentase_potongan'] > 0) ? (float)$row['persentase_potongan'] : $ratePotongan;
            $itemPersenInv = (isset($row['persentase_hak_investor']) && (float)$row['persentase_hak_investor'] > 0) ? (float)$row['persentase_hak_investor'] : $persenInvSplit;
            $itemPersenOut = 100.00 - $itemPersenInv;

            $pot10 = (isset($row['nominal_potongan']) && (float)$row['nominal_potongan'] > 0) ? (float)$row['nominal_potongan'] : round($nominal_omzet * ($itemRatePot / 100.0), 2);
            $hakInv = round($pot10 * ($itemPersenInv / 100.0), 2);
            $hakOut = round($pot10 * ($itemPersenOut / 100.0), 2);
            $bersihOut = $omzet - $pot10 + $hakOut;

            $totOmzet += $omzet;
            $totPotongan += $pot10;
            $totHakInvestor += $hakInv;
            $totHakOutlet += $hakOut;

            $items[] = [
                'id_laporan' => (int)$row['id_laporan'],
                'tgl_raw' => $row['tanggal_omzet'],
                'tgl_formatted' => $tglStr,
                'omzet' => $omzet,
                'rate_potongan' => $itemRatePot,
                'persen_investor' => $itemPersenInv,
                'persen_outlet' => $itemPersenOut,
                'potongan_10' => $pot10,
                'hak_investor' => $hakInv,
                'hak_outlet' => $hakOut,
                'bersih_outlet' => $bersihOut
            ];
        }
    }

    $distinctPot = count(array_unique(array_column($items, 'rate_potongan')));
    $distinctInv = count(array_unique(array_column($items, 'persen_investor')));

    $ratePotHeaderStr = ($distinctPot > 1) ? 'Variatif' : ($items[0]['rate_potongan'] ?? $ratePotongan) . '%';
    $persenInvHeaderStr = ($distinctInv > 1) ? 'Variatif' : ($items[0]['persen_investor'] ?? $persenInvSplit) . '%';
    $persenOutHeaderStr = ($distinctInv > 1) ? 'Variatif' : ($items[0]['persen_outlet'] ?? $persenOutSplit) . '%';

    echo json_encode([
        'success' => true,
        'nama_outlet' => $namaOutlet,
        'rate_potongan' => $ratePotHeaderStr,
        'persen_inv' => $persenInvHeaderStr,
        'persen_out' => $persenOutHeaderStr,
        'items' => $items,
        'summary' => [
            'total_omzet' => $totOmzet,
            'total_potongan' => $totPotongan,
            'total_hak_investor' => $totHakInvestor,
            'total_hak_outlet' => $totHakOutlet
        ]
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;
