<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once(__DIR__ . "/../../../config/setting.php");

$user = User::user();
if (!$user) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);
$role = strtolower($user['role'] ?? 'investor');

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$investorId = 0;
$persenInvestor = 50.00; // Default 50%
$targetOutletId = 0;

if ($role === 'investor') {
    $resInv = $db->query("SELECT id_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
    if ($resInv && $resInv->num_rows > 0) {
        $rowInv = $resInv->fetch_assoc();
        $investorId = (int)$rowInv['id_investor'];
        $persenInvestor = 50.00;
    }
} else {
    $resOut = $db->query("SELECT o.id_outlet, o.id_investor, o.persentase_potongan, IFNULL(o.persentase_hak_investor, 50.00) as persentase_hak_investor FROM outlet o WHERE o.id_users = {$userId} LIMIT 1");
    if ($resOut && $resOut->num_rows > 0) {
        $rowOut = $resOut->fetch_assoc();
        $investorId = (int)$rowOut['id_investor'];
        $persenInvestor = (float)($rowOut['persentase_hak_investor'] ?? 50.00);
        $targetOutletId = (int)$rowOut['id_outlet'];
    }
}
$persenOutletBagiHasil = 100.00 - $persenInvestor; // 50%

$potonganGlobal = (float)($rowOut['persentase_potongan'] ?? 10.00);

// Filter Logic (Outlet, Rentang Tanggal, Bulan, Tahun)
$selectedOutletId   = isset($_GET['outlet_id']) ? (int)$_GET['outlet_id'] : (isset($_GET['id_outlet']) ? (int)$_GET['id_outlet'] : (isset($_GET['outlet']) ? (int)$_GET['outlet'] : 0));
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;


$checkBulan = ($selectedBulan > 0) ? $selectedBulan : (int)date('n');
$checkTahun = ($selectedTahun > 0) ? $selectedTahun : (int)date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $checkBulan, $checkTahun);
$lastDayDateStr = sprintf('%04d-%02d-%02d', $checkTahun, $checkBulan, $daysInMonth);

$rows = [];
$totOmzet = 0;
$totPotongan10 = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;
$hasAnyLastDayDone = false;

$whereConditions = ($role === 'investor') ? ["o.id_investor = {$investorId}"] : ["o.id_outlet = {$targetOutletId}"];
$selectedOutletNama = '';

if ($selectedOutletId > 0 && $role === 'investor') {
    $whereConditions[0] = "o.id_outlet = {$selectedOutletId}";
    $resOneOut = $db->query("SELECT nama_outlet FROM outlet WHERE id_outlet = {$selectedOutletId} LIMIT 1");
    if ($resOneOut && $resOneOut->num_rows > 0) {
        $selectedOutletNama = $resOneOut->fetch_assoc()['nama_outlet'];
    }
} elseif ($role === 'outlet' && $targetOutletId > 0) {
    $resOneOut = $db->query("SELECT nama_outlet FROM outlet WHERE id_outlet = {$targetOutletId} LIMIT 1");
    if ($resOneOut && $resOneOut->num_rows > 0) {
        $selectedOutletNama = $resOneOut->fetch_assoc()['nama_outlet'];
    }
}

$periodeParts = [];

if (!empty($selectedTglMulai) && !empty($selectedTglSelesai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConditions[] = "l.tanggal_omzet BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
    
    $t1 = strtotime($selectedTglMulai);
    $t2 = strtotime($selectedTglSelesai);
    $d1 = date('j', $t1) . ' ' . ($bulanIndo[(int)date('n', $t1)] ?? '') . ' ' . date('Y', $t1);
    $d2 = date('j', $t2) . ' ' . ($bulanIndo[(int)date('n', $t2)] ?? '') . ' ' . date('Y', $t2);

    if ($selectedTglMulai === $selectedTglSelesai) {
        $periodeParts[] = $d1;
    } else {
        $periodeParts[] = $d1 . ' - ' . $d2;
    }
} elseif (!empty($selectedTglMulai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $whereConditions[] = "l.tanggal_omzet >= '{$safeMulai}'";
    $t1 = strtotime($selectedTglMulai);
    $d1 = date('j', $t1) . ' ' . ($bulanIndo[(int)date('n', $t1)] ?? '') . ' ' . date('Y', $t1);
    $periodeParts[] = 'Mulai ' . $d1;
} elseif (!empty($selectedTglSelesai)) {
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConditions[] = "l.tanggal_omzet <= '{$safeSelesai}'";
    $t2 = strtotime($selectedTglSelesai);
    $d2 = date('j', $t2) . ' ' . ($bulanIndo[(int)date('n', $t2)] ?? '') . ' ' . date('Y', $t2);
    $periodeParts[] = 'Sampai ' . $d2;
} else {
    if ($selectedBulan > 0) {
        $whereConditions[] = "MONTH(l.tanggal_omzet) = {$selectedBulan}";
        $periodeParts[] = $bulanIndo[$selectedBulan] ?? '';
    }
    if ($selectedTahun > 0) {
        $whereConditions[] = "YEAR(l.tanggal_omzet) = {$selectedTahun}";
        $periodeParts[] = $selectedTahun;
    }
}

$periodeTitleStr = !empty($periodeParts) ? implode(" ", $periodeParts) : "Semua Periode";
$displayNamaToko = (!empty($selectedOutletNama) && $selectedOutletId > 0) ? $selectedOutletNama : "Semua Toko";
$periodeLabelStr = $periodeTitleStr;

$laporanJoinConds = array_filter($whereConditions, fn($c) => strpos($c, 'o.') === false);
$joinOnClause = "o.id_outlet = l.id_outlet";
if (!empty($laporanJoinConds)) {
    $joinOnClause .= " AND " . implode(" AND ", $laporanJoinConds);
}

$sqlBagiHasil = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        o.persentase_potongan,
        IFNULL(o.persentase_hak_investor, 50.00) as persentase_hak_investor,
        IFNULL(SUM(l.nominal_omzet), 0) as total_omzet,
        IFNULL(SUM(l.nominal_potongan), 0) as total_potongan_db,
        IFNULL(SUM(ROUND(l.nominal_potongan * (IFNULL(l.persentase_hak_investor, IFNULL(o.persentase_hak_investor, 50.00)) / 100.0), 2)), 0) as total_hak_investor_db,
        IFNULL(SUM(ROUND(l.nominal_potongan * ((100.00 - IFNULL(l.persentase_hak_investor, IFNULL(o.persentase_hak_investor, 50.00))) / 100.0), 2)), 0) as total_hak_outlet_db,
        COUNT(DISTINCT l.persentase_potongan) as count_distinct_rates,
        MIN(l.persentase_potongan) as min_rate,
        MAX(l.persentase_potongan) as max_rate
    FROM outlet o
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN laporan_omzet l ON {$joinOnClause}
    WHERE {$whereConditions[0]}
    GROUP BY o.id_outlet, o.nama_outlet, o.persentase_potongan, o.persentase_hak_investor
    ORDER BY o.id_outlet DESC
";

$resBagiHasil = $db->query($sqlBagiHasil);

if ($resBagiHasil) {
    while ($row = $resBagiHasil->fetch_assoc()) {
        $nominal_omzet = (float)$row['total_omzet'];
        $idOutletRow = (int)$row['id_outlet'];
        $ratePotongan = (float)($row['persentase_potongan'] ?? 10.00);
        $rateInvestor = (float)($row['persentase_hak_investor'] ?? 50.00);
        $rateOutlet = 100.00 - $rateInvestor;

        $countRates = (int)($row['count_distinct_rates'] ?? 0);
        $minRate = (float)($row['min_rate'] ?? $ratePotongan);
        $maxRate = (float)($row['max_rate'] ?? $ratePotongan);

        if ($countRates > 1 && $minRate !== $maxRate) {
            $displayRate = "Variatif (" . $minRate . "% - " . $maxRate . "%)";
        } elseif ($minRate > 0) {
            $displayRate = $minRate . "%";
        } else {
            $displayRate = $ratePotongan . "%";
        }

        // Calculations strictly match each store's custom rate and split
        $potongan10 = (float)$row['total_potongan_db'];
        $hakInvestor = (float)$row['total_hak_investor_db'];
        $hakOutlet   = (float)$row['total_hak_outlet_db'];
        $hasAnyLastDayDone = true;

        $row['persentase_potongan'] = $ratePotongan;
        $row['display_rate'] = $displayRate;
        $row['persentase_hak_investor'] = $rateInvestor;
        $row['potongan_10'] = $potongan10;
        $row['hak_investor'] = $hakInvestor;
        $row['hak_outlet'] = $hakOutlet;
        $row['total_bersih_outlet'] = ($nominal_omzet - $potongan10) + $hakOutlet;
        $row['is_last_day_done'] = true;

        $totOmzet += $nominal_omzet;
        $totPotongan10 += $potongan10;
        $totHakInvestor += $hakInvestor;
        $totHakOutlet += $hakOutlet;

        $rows[] = $row;
    }
}

$countOutlet = count($rows);

// HTML Template for Dompdf
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bagi Hasil - <?= htmlspecialchars($periodeLabelStr); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #7D0A0A;
            padding-bottom: 10px;
        }
        .header-title {
            color: #7D0A0A;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header-subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .meta-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .meta-box td {
            padding: 8px 12px;
            vertical-align: top;
        }
        .meta-label {
            color: #64748b;
            font-weight: bold;
            width: 120px;
        }
        .meta-value {
            color: #0f172a;
            font-weight: bold;
        }
        
        /* Summary Metric Cards Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-card {
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #ffffff;
            text-align: center;
        }
        .summary-card-title {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-card-val {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #7D0A0A;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 8px;
            text-align: left;
            border: 1px solid #7D0A0A;
        }
        .data-table td {
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #dc2626; }
        .text-success { color: #16a34a; }
        .text-warning { color: #d97706; }

        .footer-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .footer-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <h1 class="header-title">TOKO MADURA</h1>
                <div class="header-subtitle">Laporan Rekapitulasi Pembagian Bagi Hasil (Investor & Outlet)</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-size: 9px; color: #64748b;">Tanggal Cetak:</div>
                <div style="font-weight: bold; color: #0f172a;"><?= date('d/m/Y H:i'); ?> WIB</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Info -->
    <table class="meta-box">
        <tr>
            <td style="width: 50%;">
                <table style="width: 100%;">
                    <tr>
                        <td class="meta-label">Akses Role</td>
                        <td class="meta-value">: <?= strtoupper($role); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Jumlah Outlet</td>
                        <td class="meta-value">: <?= $countOutlet; ?> Outlet Terdaftar</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table style="width: 100%;">
                    <tr>
                        <td class="meta-label">Periode Laporan</td>
                        <td class="meta-value">: <?= htmlspecialchars($periodeTitleStr); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Nama Toko</td>
                        <td class="meta-value">: <?= htmlspecialchars($displayNamaToko); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 4 Metric Cards Row -->
    <table class="summary-table">
        <tr>
            <td style="width: 25%; padding: 4px;">
                <div class="summary-card" style="border-top: 3px solid #0d6efd;">
                    <div class="summary-card-title">Total Omzet Toko (100%)</div>
                    <div class="summary-card-val" style="color: #0d6efd;">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></div>
                </div>
            </td>
            <td style="width: 25%; padding: 4px;">
                <div class="summary-card" style="border-top: 3px solid #dc2626;">
                    <div class="summary-card-title">Potongan Outlet</div>
                    <div class="summary-card-val text-danger">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totPotongan10, 0, ',', '.') : '-'; ?>
                    </div>
                </div>
            </td>
            <td style="width: 25%; padding: 4px;">
                <div class="summary-card" style="border-top: 3px solid #16a34a;">
                    <div class="summary-card-title">Hak Investor</div>
                    <div class="summary-card-val text-success">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakInvestor, 0, ',', '.') : '-'; ?>
                    </div>
                </div>
            </td>
            <td style="width: 25%; padding: 4px;">
                <div class="summary-card" style="border-top: 3px solid #d97706;">
                    <div class="summary-card-title">Hak Outlet</div>
                    <div class="summary-card-val text-warning">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakOutlet, 0, ',', '.') : '-'; ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Breakdown Table Per Outlet -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 140px;">Nama Outlet</th>
                <th class="text-end">Total Omzet (100%)</th>
                <th class="text-end">Potongan Outlet</th>
                <th class="text-end">Hak Investor</th>
                <th class="text-end">Hak Outlet</th>
                <th class="text-end">Bersih Outlet Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)) : ?>
                <?php $no = 1; foreach ($rows as $r) : ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td>
                            <strong><?= htmlspecialchars($r['nama_outlet']); ?></strong>
                        </td>
                        <td class="text-end fw-bold">Rp <?= number_format($r['total_omzet'], 0, ',', '.'); ?></td>
                        <td class="text-end text-danger fw-bold">
                            <?= $r['is_last_day_done'] ? 'Rp ' . number_format($r['potongan_10'], 0, ',', '.') : '-'; ?>
                        </td>
                        <td class="text-end text-success fw-bold">
                            <?= $r['is_last_day_done'] ? 'Rp ' . number_format($r['hak_investor'], 0, ',', '.') : '-'; ?>
                        </td>
                        <td class="text-end text-warning fw-bold">
                            <?= $r['is_last_day_done'] ? 'Rp ' . number_format($r['hak_outlet'], 0, ',', '.') : '-'; ?>
                        </td>
                        <td class="text-end fw-bold">Rp <?= number_format($r['total_bersih_outlet'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                        Belum ada data outlet / omzet pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($rows)) : ?>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="2" class="text-end" style="padding: 9px; font-size: 10px; text-transform: uppercase;">TOTAL KESELURUHAN:</td>
                    <td class="text-end" style="padding: 9px;">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></td>
                    <td class="text-end text-danger" style="padding: 9px;">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totPotongan10, 0, ',', '.') : '-'; ?>
                    </td>
                    <td class="text-end text-success" style="padding: 9px; font-size: 11px;">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakInvestor, 0, ',', '.') : '-'; ?>
                    </td>
                    <td class="text-end text-warning" style="padding: 9px; font-size: 11px;">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakOutlet, 0, ',', '.') : '-'; ?>
                    </td>
                    <td class="text-end" style="padding: 9px;">Rp <?= number_format($totOmzet - $totHakInvestor, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>



</body>
</html>
<?php
$html = ob_get_clean();

// Generate Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("Laporan_Bagi_Hasil_{$selectedBulan}_{$selectedTahun}.pdf", ["Attachment" => 0]);
exit;
