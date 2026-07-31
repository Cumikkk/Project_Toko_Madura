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
        SELECT o.nama_outlet, o.persentase_potongan, i.persen_bagian_investor 
        FROM outlet o 
        LEFT JOIN investor i ON o.id_investor = i.id_investor 
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
    $persenInvSplit = isset($rowOutInfo['persen_bagian_investor']) ? (float)$rowOutInfo['persen_bagian_investor'] : 50.00;
    $persenOutSplit = 100.00 - $persenInvSplit;

    // Determine Month and Year for full monthly daily breakdown (Tgl 1 s.d. Tgl 31)
    $reqBulan = $bulan;
    $reqTahun = $tahun;

    if ($reqBulan <= 0 && !empty($tglMulai)) {
        $reqBulan = (int)date('n', strtotime($tglMulai));
    }
    if ($reqTahun <= 0 && !empty($tglMulai)) {
        $reqTahun = (int)date('Y', strtotime($tglMulai));
    }

    if ($reqBulan <= 0) $reqBulan = (int)date('n');
    if ($reqTahun <= 0) $reqTahun = (int)date('Y');

    // Always fetch ALL days in the month for full daily breakdown
    $whereConds = [
        "l.id_outlet = {$idOutlet}",
        "MONTH(l.periode_laporan) = {$reqBulan}",
        "YEAR(l.periode_laporan) = {$reqTahun}"
    ];

    $whereSql = implode(" AND ", $whereConds);
    $sql = "SELECT id_laporan, periode_laporan, omzet, nominal_potongan, waktu_input FROM laporan_omzet l WHERE {$whereSql} ORDER BY periode_laporan ASC";
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
            $omzet = (float)$row['omzet'];
            $tglStr = date('d', strtotime($row['periode_laporan'])) . ' ' . 
                      ($bulanIndo[(int)date('n', strtotime($row['periode_laporan']))] ?? '') . ' ' . 
                      date('Y', strtotime($row['periode_laporan']));

            $pot10 = round($omzet * ($ratePotongan / 100.0), 2);
            $hakInv = round($pot10 * ($persenInvSplit / 100.0), 2);
            $hakOut = round($pot10 * ($persenOutSplit / 100.0), 2);
            $bersihOut = $omzet - $pot10 + $hakOut;

            $totOmzet += $omzet;
            $totPotongan += $pot10;
            $totHakInvestor += $hakInv;
            $totHakOutlet += $hakOut;

            $items[] = [
                'id_laporan' => (int)$row['id_laporan'],
                'tgl_raw' => $row['periode_laporan'],
                'tgl_formatted' => $tglStr,
                'omzet' => $omzet,
                'potongan_10' => $pot10,
                'hak_investor' => $hakInv,
                'hak_outlet' => $hakOut,
                'bersih_outlet' => $bersihOut
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'nama_outlet' => $namaOutlet,
        'rate_potongan' => $ratePotongan,
        'persen_inv' => $persenInvSplit,
        'persen_out' => $persenOutSplit,
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
