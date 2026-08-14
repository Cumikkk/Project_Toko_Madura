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
$role = strtolower($user['role'] ?? 'outlet');

// Get Outlet Info for logged-in user or requested outlet
$idOutlet = isset($_GET['id_outlet']) ? (int)$_GET['id_outlet'] : (isset($_GET['outlet_id']) ? (int)$_GET['outlet_id'] : 0);

if ($idOutlet > 0) {
    $resOut = $db->query("
        SELECT o.id_outlet, o.nama_outlet, o.kecamatan, u_out.alamat_lengkap as alamat_outlet, o.persentase_potongan, IFNULL(o.persentase_hak_investor, 50.00) as persentase_hak_investor, u.nama_lengkap as nama_investor, u_out.nama_lengkap as nama_pengelola, u_out.username as username_outlet
        FROM outlet o
        LEFT JOIN users u_out ON o.id_users = u_out.id_users
        LEFT JOIN investor i ON o.id_investor = i.id_investor
        LEFT JOIN users u ON i.id_users = u.id_users
        WHERE o.id_outlet = {$idOutlet}
        LIMIT 1
    ");
} else {
    $resOut = $db->query("
        SELECT o.id_outlet, o.nama_outlet, o.kecamatan, u_out.alamat_lengkap as alamat_outlet, o.persentase_potongan, IFNULL(o.persentase_hak_investor, 50.00) as persentase_hak_investor, u.nama_lengkap as nama_investor, u_out.nama_lengkap as nama_pengelola, u_out.username as username_outlet
        FROM outlet o
        LEFT JOIN users u_out ON o.id_users = u_out.id_users
        LEFT JOIN investor i ON o.id_investor = i.id_investor
        LEFT JOIN users u ON i.id_users = u.id_users
        WHERE o.id_users = {$userId}
        LIMIT 1
    ");
}

$outlet = ($resOut && $resOut->num_rows > 0) ? $resOut->fetch_assoc() : null;

if (!$outlet) {
    die("Outlet tidak ditemukan atau akun belum terhubung dengan outlet.");
}

$idOutlet = (int)$outlet['id_outlet'];

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Filter Logic (Rentang Tanggal tgl_mulai & tgl_selesai)
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$whereConditions = ["id_outlet = {$idOutlet}"];
$labelParts = [];

if (!empty($selectedTglMulai) && !empty($selectedTglSelesai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConditions[] = "tanggal_omzet BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
    
    if ($selectedTglMulai === $selectedTglSelesai) {
        $labelParts[] = date('d/m/Y', strtotime($selectedTglMulai));
    } else {
        $labelParts[] = date('d/m/Y', strtotime($selectedTglMulai)) . ' - ' . date('d/m/Y', strtotime($selectedTglSelesai));
    }
} elseif (!empty($selectedTglMulai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $whereConditions[] = "tanggal_omzet >= '{$safeMulai}'";
    $labelParts[] = 'Mulai ' . date('d/m/Y', strtotime($selectedTglMulai));
} elseif (!empty($selectedTglSelesai)) {
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConditions[] = "tanggal_omzet <= '{$safeSelesai}'";
    $labelParts[] = 's/d ' . date('d/m/Y', strtotime($selectedTglSelesai));
} else {
    if ($selectedBulan > 0) {
        $whereConditions[] = "MONTH(tanggal_omzet) = {$selectedBulan}";
        $labelParts[] = $bulanIndo[$selectedBulan] ?? '';
    }
    if ($selectedTahun > 0) {
        $whereConditions[] = "YEAR(tanggal_omzet) = {$selectedTahun}";
        $labelParts[] = $selectedTahun;
    }
}

$periodeLabelStr = !empty($labelParts) ? implode(" ", $labelParts) : "Semua Periode Transaksi";
$whereSql = "WHERE " . implode(" AND ", $whereConditions);

$sqlOmzet = "SELECT * FROM laporan_omzet {$whereSql} ORDER BY tanggal_omzet ASC";
$resOmzet = $db->query($sqlOmzet);

$dailyRows = [];
$totOmzetKotor = 0;
$totPotonganNominal = 0;
$totHakInvestorNominal = 0;
$totHakOutletNominal = 0;
$totOmzetSisaToko = 0;
$totPendapatanBersihOutlet = 0;

$fallbackPotongan = (float)($outlet['persentase_potongan'] ?? 10.00);
$fallbackInvestorPct = (float)($outlet['persentase_hak_investor'] ?? 50.00);

if ($resOmzet) {
    while ($row = $resOmzet->fetch_assoc()) {
        $omzetKotor = (float)($row['nominal_omzet'] ?? $row['omzet'] ?? 0);
        $potPct = isset($row['persentase_potongan']) ? (float)$row['persentase_potongan'] : $fallbackPotongan;
        
        $nomPotongan = isset($row['nominal_potongan']) && (float)$row['nominal_potongan'] > 0 
            ? (float)$row['nominal_potongan'] 
            : round($omzetKotor * ($potPct / 100.0), 2);

        $invPct = isset($row['persentase_hak_investor']) ? (float)$row['persentase_hak_investor'] : $fallbackInvestorPct;
        $outPct = 100.00 - $invPct;

        $nomHakInvestor = round($nomPotongan * ($invPct / 100.0), 2);
        $nomHakOutlet   = round($nomPotongan * ($outPct / 100.0), 2);
        $omzetSisaToko  = round($omzetKotor - $nomPotongan, 2);
        $pendapatanBersihOutlet = round($omzetSisaToko + $nomHakOutlet, 2);

        $rowCalculated = [
            'tanggal_omzet' => $row['tanggal_omzet'],
            'created_at' => $row['created_at'],
            'omzet_kotor' => $omzetKotor,
            'potongan_pct' => $potPct,
            'nominal_potongan' => $nomPotongan,
            'hak_investor_pct' => $invPct,
            'nominal_hak_investor' => $nomHakInvestor,
            'hak_outlet_pct' => $outPct,
            'nominal_hak_outlet' => $nomHakOutlet,
            'omzet_sisa_toko' => $omzetSisaToko,
            'pendapatan_bersih_outlet' => $pendapatanBersihOutlet
        ];

        $dailyRows[] = $rowCalculated;

        $totOmzetKotor += $omzetKotor;
        $totPotonganNominal += $nomPotongan;
        $totHakInvestorNominal += $nomHakInvestor;
        $totHakOutletNominal += $nomHakOutlet;
        $totOmzetSisaToko += $omzetSisaToko;
        $totPendapatanBersihOutlet += $pendapatanBersihOutlet;
    }
}

$totalHari = count($dailyRows);

// HTML Template for Dompdf
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Neraca Keuangan - <?= htmlspecialchars($outlet['nama_outlet']); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2.5px solid #7D0A0A;
            padding-bottom: 8px;
        }
        .header-title {
            font-size: 20px;
            font-weight: 900;
            color: #7D0A0A;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 11px;
            font-weight: bold;
            color: #475569;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .info-box td {
            padding: 6px 10px;
            vertical-align: top;
            font-size: 9.5px;
        }
        .info-label {
            color: #64748b;
            font-weight: bold;
        }
        .info-value {
            color: #0f172a;
            font-weight: bold;
        }

        .section-heading {
            font-size: 11px;
            font-weight: bold;
            color: #7D0A0A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 8px;
            background-color: #fee2e2;
            border-left: 4px solid #7D0A0A;
            margin-bottom: 8px;
        }

        .neraca-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .neraca-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
        }
        .neraca-card {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
        }
        .neraca-card th {
            background-color: #7D0A0A;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
        }
        .neraca-card td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
        }
        .neraca-card tr:last-child td {
            border-bottom: none;
        }
        .neraca-card .row-total td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 1.5px solid #0f172a;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #334155;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 6px 6px;
            text-align: center;
            border: 1px solid #334155;
        }
        .data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #dc2626; }
        .text-success { color: #16a34a; }
        .text-primary { color: #2563eb; }

        .signature-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 9px;
        }
        .signature-space {
            height: 45px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 65%;">
                <h1 class="header-title">TOKO MADURA</h1>
                <div class="header-subtitle">Laporan Neraca Keuangan &amp; Rincian Harian Outlet</div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: bottom;">
                <div style="font-size: 8.5px; color: #64748b;">Kode Dokumen:</div>
                <div style="font-weight: bold; color: #0f172a; font-size: 10px;">FIN-OUTLET-<?= sprintf('%04d', $outlet['id_outlet']); ?>/<?= date('Ym'); ?></div>
            </td>
        </tr>
    </table>

    <!-- Outlet Metadata Info Box -->
    <table class="info-box">
        <tr>
            <td style="width: 50%; border-right: 1px dashed #cbd5e1;">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td class="info-label" style="width: 35%;">Nama Outlet Toko</td>
                        <td class="info-label" style="width: 3%;">:</td>
                        <td class="info-value" style="width: 62%;"><?= htmlspecialchars($outlet['nama_outlet']); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Pengelola / Kasir</td>
                        <td class="info-label">:</td>
                        <td class="info-value"><?= htmlspecialchars($outlet['nama_pengelola'] ?: '-'); ?> (<?= htmlspecialchars($outlet['username_outlet'] ? '@'.$outlet['username_outlet'] : '-'); ?>)</td>
                    </tr>
                    <tr>
                        <td class="info-label">Kecamatan / Alamat</td>
                        <td class="info-label">:</td>
                        <td class="info-value"><?= htmlspecialchars($outlet['kecamatan'] ?: '-'); ?> - <?= htmlspecialchars($outlet['alamat_outlet'] ?: '-'); ?></td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td class="info-label" style="width: 38%;">Investor Mitra</td>
                        <td class="info-label" style="width: 3%;">:</td>
                        <td class="info-value" style="width: 59%;"><?= htmlspecialchars($outlet['nama_investor'] ?: 'Mitra Pusat'); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Periode Laporan</td>
                        <td class="info-label">:</td>
                        <td class="info-value"><?= htmlspecialchars($periodeLabelStr); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Skema Bagi Hasil</td>
                        <td class="info-label">:</td>
                        <td class="info-value">Potongan <?= number_format($fallbackPotongan, 1); ?>% | Inv <?= number_format($fallbackInvestorPct, 1); ?>% : Out <?= number_format(100 - $fallbackInvestorPct, 1); ?>%</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- RINGKASAN NERACA KEUANGAN SEDERHANA -->
    <div class="section-heading">I. RINGKASAN NERACA KEUANGAN SEDERHANA (BALANCE SHEET SUMMARY)</div>
    
    <table class="neraca-table">
        <tr>
            <!-- DEBET / PENDAPATAN (AKTIVA) -->
            <td>
                <table class="neraca-card">
                    <thead>
                        <tr>
                            <th colspan="2">AKTIVA / PENDAPATAN OMZET PENJUALAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Omzet Penjualan Kotor (100%)</td>
                            <td class="text-end fw-bold text-success">Rp <?= number_format($totOmzetKotor, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Jumlah Hari Transaksi Terinput</td>
                            <td class="text-end fw-bold"><?= $totalHari; ?> Hari</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Rata-Rata Omzet Kotor per Hari</td>
                            <td class="text-end fw-bold">Rp <?= number_format($totalHari > 0 ? $totOmzetKotor / $totalHari : 0, 0, ',', '.'); ?></td>
                        </tr>
                        <tr class="row-total">
                            <td>TOTAL DEBET PENDAPATAN (AKTIVA)</td>
                            <td class="text-end text-success">Rp <?= number_format($totOmzetKotor, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- KREDIT / ALOKASI & DISTRIBUSI HASIL (PASIVA) -->
            <td>
                <table class="neraca-card">
                    <thead>
                        <tr>
                            <th colspan="2">PASIVA / ALOKASI BAGI HASIL &amp; OPERASIONAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Potongan Omzet Toko (<?= number_format($fallbackPotongan, 1); ?>%)</td>
                            <td class="text-end fw-bold text-danger">Rp <?= number_format($totPotonganNominal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 16px; color: #475569;">- Hak Bagi Hasil Investor (<?= number_format($fallbackInvestorPct, 1); ?>%)</td>
                            <td class="text-end fw-bold text-primary">Rp <?= number_format($totHakInvestorNominal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 16px; color: #475569;">- Hak Bagi Hasil Outlet (<?= number_format(100 - $fallbackInvestorPct, 1); ?>%)</td>
                            <td class="text-end fw-bold text-success">Rp <?= number_format($totHakOutletNominal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Sisa Omzet Kotor Kasir Toko</td>
                            <td class="text-end fw-bold">Rp <?= number_format($totOmzetSisaToko, 0, ',', '.'); ?></td>
                        </tr>
                        <tr class="row-total">
                            <td>TOTAL ALOKASI PASIVA (BALANCE)</td>
                            <td class="text-end text-danger">Rp <?= number_format($totOmzetKotor, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- HIGHLIGHT SUMMARY BOX -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px; background-color: #f1f5f9; border: 1.5px solid #94a3b8; border-radius: 6px;">
        <tr>
            <td style="width: 50%; padding: 8px 12px; border-right: 1px solid #cbd5e1;">
                <div style="font-size: 8.5px; font-weight: bold; color: #475569; text-uppercase;">TOTAL HAK BAGI HASIL INVESTOR</div>
                <div style="font-size: 15px; font-weight: 900; color: #2563eb; margin-top: 2px;">
                    Rp <?= number_format($totHakInvestorNominal, 0, ',', '.'); ?>
                </div>
            </td>
            <td style="width: 50%; padding: 8px 12px;">
                <div style="font-size: 8.5px; font-weight: bold; color: #475569; text-uppercase;">TOTAL PENDAPATAN BERSIH OUTLET (SISA + HAK OUTLET)</div>
                <div style="font-size: 15px; font-weight: 900; color: #16a34a; margin-top: 2px;">
                    Rp <?= number_format($totPendapatanBersihOutlet, 0, ',', '.'); ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- RINCIAN HARIAN PENJUALAN & BAGI HASIL -->
    <div class="section-heading">II. RINCIAN HARIAN PENJUALAN &amp; ALOKASI BAGI HASIL (DAILY JOURNAL)</div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 90px;">Omzet Kotor (Rp)</th>
                <th style="width: 45px;">Pot. (%)</th>
                <th style="width: 80px;">Nominal Potongan</th>
                <th style="width: 80px;">Hak Investor (Rp)</th>
                <th style="width: 80px;">Hak Outlet (Rp)</th>
                <th style="width: 90px;">Sisa Omzet Toko</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($dailyRows)) : ?>
                <?php $no = 1; foreach ($dailyRows as $d) : ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td class="text-center fw-bold"><?= date('d/m/Y', strtotime($d['tanggal_omzet'])); ?></td>
                        <td class="text-end fw-bold text-success">Rp <?= number_format($d['omzet_kotor'], 0, ',', '.'); ?></td>
                        <td class="text-center"><?= number_format($d['potongan_pct'], 1); ?>%</td>
                        <td class="text-end text-danger">Rp <?= number_format($d['nominal_potongan'], 0, ',', '.'); ?></td>
                        <td class="text-end text-primary">Rp <?= number_format($d['nominal_hak_investor'], 0, ',', '.'); ?></td>
                        <td class="text-end text-success">Rp <?= number_format($d['nominal_hak_outlet'], 0, ',', '.'); ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($d['omzet_sisa_toko'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 16px; color: #64748b;">
                        Belum ada laporan omzet harian yang terinput pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold; font-size: 9px;">
                <td colspan="2" class="text-end" style="text-uppercase; padding: 6px 8px;">TOTAL KESELURUHAN:</td>
                <td class="text-end text-success" style="padding: 6px;">Rp <?= number_format($totOmzetKotor, 0, ',', '.'); ?></td>
                <td class="text-center">-</td>
                <td class="text-end text-danger" style="padding: 6px;">Rp <?= number_format($totPotonganNominal, 0, ',', '.'); ?></td>
                <td class="text-end text-primary" style="padding: 6px;">Rp <?= number_format($totHakInvestorNominal, 0, ',', '.'); ?></td>
                <td class="text-end text-success" style="padding: 6px;">Rp <?= number_format($totHakOutletNominal, 0, ',', '.'); ?></td>
                <td class="text-end fw-bold" style="padding: 6px;">Rp <?= number_format($totOmzetSisaToko, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- LEMBAR PENGESAHAN & TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td>
                <div>Dibuat Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Pengelola / Kasir Outlet</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($outlet['nama_pengelola'] ?: 'Pengelola Outlet'); ?></div>
                <div style="color: #64748b; font-size: 8px;">Outlet <?= htmlspecialchars($outlet['nama_outlet']); ?></div>
            </td>
            <td>
                <div>Diverifikasi Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Investor Mitra</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($outlet['nama_investor'] ?: 'Investor Mitra'); ?></div>
                <div style="color: #64748b; font-size: 8px;">Mitra Pemilik Investasi</div>
            </td>
            <td>
                <div>Disetujui Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Master Admin System</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Manajemen Toko Madura</div>
                <div style="color: #64748b; font-size: 8px;">Dicetak: <?= date('d/m/Y H:i'); ?> WIB</div>
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
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$cleanFilename = preg_replace('/[^a-zA-Z0-9_]/', '_', $outlet['nama_outlet']);
$dompdf->stream("Neraca_Keuangan_{$cleanFilename}.pdf", ["Attachment" => 0]);
exit;
