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
    $resInv = $db->query("SELECT id_investor, persen_bagian_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
    if ($resInv && $resInv->num_rows > 0) {
        $rowInv = $resInv->fetch_assoc();
        $investorId = (int)$rowInv['id_investor'];
        $persenInvestor = (float)$rowInv['persen_bagian_investor'];
    }
} else {
    $resOut = $db->query("SELECT o.id_outlet, o.id_investor, i.persen_bagian_investor FROM outlet o LEFT JOIN investor i ON o.id_investor = i.id_investor WHERE o.id_users = {$userId} LIMIT 1");
    if ($resOut && $resOut->num_rows > 0) {
        $rowOut = $resOut->fetch_assoc();
        $investorId = (int)$rowOut['id_investor'];
        $persenInvestor = (float)($rowOut['persen_bagian_investor'] ?? 50.00);
        $targetOutletId = (int)$rowOut['id_outlet'];
    }
}
$persenOutletBagiHasil = 100.00 - $persenInvestor; // 50%

// Get global platform deduction percentage
$resSet = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'potongan_global' LIMIT 1");
$potonganGlobal = 10.00;
if ($resSet && $resSet->num_rows > 0) {
    $potonganGlobal = (float)$resSet->fetch_assoc()['nilai'];
}

// Separate Month & Year Filter
$selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

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
if ($selectedBulan > 0) {
    $whereConditions[] = "MONTH(l.periode_laporan) = {$selectedBulan}";
}
if ($selectedTahun > 0) {
    $whereConditions[] = "YEAR(l.periode_laporan) = {$selectedTahun}";
}

$sqlBagiHasil = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        IFNULL(SUM(l.omzet), 0) as total_omzet,
        IFNULL(SUM(l.nominal_potongan), 0) as total_potongan_db
    FROM outlet o
    LEFT JOIN laporan_omzet l ON o.id_outlet = l.id_outlet AND " . implode(" AND ", array_filter($whereConditions, fn($c) => strpos($c, 'o.') === false)) . "
    WHERE {$whereConditions[0]}
    GROUP BY o.id_outlet
    ORDER BY o.id_outlet DESC
";

$resBagiHasil = $db->query($sqlBagiHasil);

if ($resBagiHasil) {
    while ($row = $resBagiHasil->fetch_assoc()) {
        $omzet = (float)$row['total_omzet'];
        $idOutletRow = (int)$row['id_outlet'];
        
        if ($selectedBulan > 0) {
            $chkLast = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutletRow} AND periode_laporan = '{$lastDayDateStr}' LIMIT 1");
            $isLastDayDone = ($chkLast && $chkLast->num_rows > 0);

            if ($isLastDayDone) {
                $hasAnyLastDayDone = true;
                $potongan10 = round($omzet * ($potonganGlobal / 100.0), 2);
                $hakInvestor = round($potongan10 * ($persenInvestor / 100.0), 2);
                $hakOutlet = round($potongan10 * ($persenOutletBagiHasil / 100.0), 2);
            } else {
                $potongan10 = 0.00;
                $hakInvestor = 0.00;
                $hakOutlet = 0.00;
            }
        } else {
            $potongan10 = (float)$row['total_potongan_db'];
            $hakInvestor = round($potongan10 * ($persenInvestor / 100.0), 2);
            $hakOutlet = round($potongan10 * ($persenOutletBagiHasil / 100.0), 2);
            $isLastDayDone = true;
        }

        $row['potongan_10'] = $potongan10;
        $row['hak_investor'] = $hakInvestor;
        $row['hak_outlet'] = $hakOutlet;
        $row['total_bersih_outlet'] = $omzet - $potongan10;
        $row['is_last_day_done'] = $isLastDayDone;

        $totOmzet += $omzet;
        $totPotongan10 += $potongan10;
        $totHakInvestor += $hakInvestor;
        $totHakOutlet += $hakOutlet;

        $rows[] = $row;
    }
}

$periodeLabelStr = ($selectedBulan > 0 ? ($bulanIndo[$selectedBulan] ?? '') . ' ' : 'Semua Bulan ') . ($selectedTahun > 0 ? $selectedTahun : '');
if ($selectedBulan === 0 && $selectedTahun === 0) {
    $periodeLabelStr = 'Semua Periode';
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
                        <td class="meta-value">: <?= strtoupper($role); ?> PANEL</td>
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
                        <td class="meta-value">: <?= htmlspecialchars($periodeLabelStr); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Skema Bagi Hasil</td>
                        <td class="meta-value">: 50% Investor : 50% Outlet (Dari Potongan 10%)</td>
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
                    <div class="summary-card-title">Potongan (10%)</div>
                    <div class="summary-card-val text-danger">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totPotongan10, 0, ',', '.') : '-'; ?>
                    </div>
                </div>
            </td>
            <td style="width: 25%; padding: 4px;">
                <div class="summary-card" style="border-top: 3px solid #16a34a;">
                    <div class="summary-card-title">Hak Investor (50%)</div>
                    <div class="summary-card-val text-success">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakInvestor, 0, ',', '.') : '-'; ?>
                    </div>
                </div>
            </td>
            <td style="width: 25%; padding: 4px;">
                <div class="summary-card" style="border-top: 3px solid #d97706;">
                    <div class="summary-card-title">Hak Outlet (50%)</div>
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
                <th class="text-end">Potongan (10%)</th>
                <th class="text-end">Hak Investor (50%)</th>
                <th class="text-end">Hak Outlet (50%)</th>
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

    <!-- Signatures -->
    <table class="footer-table">
        <tr>
            <td>
                <div>Pihak Pengelola Outlet,</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Perwakilan Outlet Toko Madura</div>
                <div style="font-size: 9px; color: #64748b;">Penanggung Jawab Operasional</div>
            </td>
            <td>
                <div>Pihak Investor Toko Madura,</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($user['nama_lengkap'] ?? 'Investor Toko Madura'); ?></div>
                <div style="font-size: 9px; color: #64748b;">Investor Utama</div>
            </td>
        </tr>
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
