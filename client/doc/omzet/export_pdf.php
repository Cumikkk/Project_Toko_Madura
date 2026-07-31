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

// Get Outlet Info for logged-in user
$resOut = $db->query("
    SELECT o.id_outlet, o.nama_outlet, o.alamat_outlet, o.persentase_potongan, IFNULL(i.persen_bagian_investor, 50.00) as persen_bagian_investor, u.nama_lengkap as nama_investor
    FROM outlet o
    LEFT JOIN investor i ON o.id_investor = i.id_investor
    LEFT JOIN users u ON i.id_users = u.id_users
    WHERE o.id_users = {$userId}
    LIMIT 1
");
$outlet = ($resOut && $resOut->num_rows > 0) ? $resOut->fetch_assoc() : null;

if (!$outlet) {
    die("Outlet tidak ditemukan atau akun belum terhubung dengan outlet.");
}

$idOutlet = (int)$outlet['id_outlet'];

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// 4 Dropdown Filter Columns (Tanggal 1-31, Minggu 1-5, Bulan, Tahun)
$selectedTglNum = isset($_GET['tanggal']) ? (int)$_GET['tanggal'] : 0;
$selectedMinggu = isset($_GET['minggu']) ? (int)$_GET['minggu'] : 0;
$selectedBulan  = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun  = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

if (!isset($_GET['tanggal']) && !isset($_GET['minggu']) && !isset($_GET['bulan']) && !isset($_GET['tahun'])) {
    $selectedBulan = (int)date('n');
    $selectedTahun = (int)date('Y');
}

$whereConditions = ["id_outlet = {$idOutlet}"];
$labelParts = [];

if ($selectedTglNum > 0) {
    $whereConditions[] = "DAY(periode_laporan) = {$selectedTglNum}";
    $labelParts[] = "Tanggal {$selectedTglNum}";
}
if ($selectedBulan > 0) {
    $whereConditions[] = "MONTH(periode_laporan) = {$selectedBulan}";
    $labelParts[] = $bulanIndo[$selectedBulan] ?? '';
}
if ($selectedTahun > 0) {
    $whereConditions[] = "YEAR(periode_laporan) = {$selectedTahun}";
    $labelParts[] = $selectedTahun;
}
if ($selectedMinggu > 0) {
    if ($selectedMinggu === 1) {
        $whereConditions[] = "DAY(periode_laporan) BETWEEN 1 AND 7";
        $labelParts[] = "(Minggu Ke-1 / Tgl 1-7)";
    } else if ($selectedMinggu === 2) {
        $whereConditions[] = "DAY(periode_laporan) BETWEEN 8 AND 14";
        $labelParts[] = "(Minggu Ke-2 / Tgl 8-14)";
    } else if ($selectedMinggu === 3) {
        $whereConditions[] = "DAY(periode_laporan) BETWEEN 15 AND 21";
        $labelParts[] = "(Minggu Ke-3 / Tgl 15-21)";
    } else if ($selectedMinggu === 4) {
        $whereConditions[] = "DAY(periode_laporan) BETWEEN 22 AND 28";
        $labelParts[] = "(Minggu Ke-4 / Tgl 22-28)";
    } else if ($selectedMinggu === 5) {
        $whereConditions[] = "DAY(periode_laporan) >= 29";
        $labelParts[] = "(Minggu Ke-5 / Tgl 29+)";
    }
}

$periodeLabelStr = !empty($labelParts) ? implode(" ", $labelParts) : "Semua Periode";
$whereSql = "WHERE " . implode(" AND ", $whereConditions);

$sqlOmzet = "SELECT * FROM laporan_omzet {$whereSql} ORDER BY periode_laporan ASC";
$resOmzet = $db->query($sqlOmzet);

$laporanList = [];
$totalOmzet = 0;
$totalNominalPotongan = 0;
$totalHariInput = 0;

if ($resOmzet) {
    while ($row = $resOmzet->fetch_assoc()) {
        $laporanList[] = $row;
        $totalOmzet += (float)$row['omzet'];
        $totalNominalPotongan += (float)($row['nominal_potongan'] ?? 0);
    }
}
$totalHariInput = count($laporanList);

// HTML Template for PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Omzet - <?= htmlspecialchars($outlet['nama_outlet']); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
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
            font-size: 10px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #7D0A0A;
        }
        .data-table td {
            padding: 7px 10px;
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
        
        .rekap-summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 30px;
        }
        .rekap-summary td {
            padding: 10px;
            border: 1px solid #e2e8f0;
            background-color: #fff;
        }
        .summary-card-title {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-card-val {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
        }

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
                <div class="header-subtitle">Laporan Omzet Operasional Harian Toko</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-size: 9px; color: #64748b;">Tanggal Cetak:</div>
                <div style="font-weight: bold; color: #0f172a;"><?= date('d/m/Y H:i'); ?> WIB</div>
            </td>
        </tr>
    </table>

    <!-- Outlet Metadata -->
    <table class="meta-box">
        <tr>
            <td style="width: 50%;">
                <table style="width: 100%;">
                    <tr>
                        <td class="meta-label">Nama Outlet</td>
                        <td class="meta-value">: <?= htmlspecialchars($outlet['nama_outlet']); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Alamat Outlet</td>
                        <td class="meta-value">: <?= htmlspecialchars($outlet['alamat_outlet'] ?: '-'); ?></td>
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
                        <td class="meta-label">Total Hari Input</td>
                        <td class="meta-value">: <?= $totalHariInput; ?> Hari</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Investor</td>
                        <td class="meta-value">: <?= htmlspecialchars($outlet['nama_investor'] ?? 'Investor'); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Data Omzet Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">No</th>
                <th style="width: 120px;">Tanggal Omzet</th>
                <th style="width: 140px;">Waktu Input System</th>
                <th class="text-end">Nominal Omzet Penjualan (Rupiah)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporanList)) : ?>
                <?php $no = 1; foreach ($laporanList as $row) : ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td>
                            <strong><?= date('d/m/Y', strtotime($row['periode_laporan'])); ?></strong>
                        </td>
                        <td style="color: #64748b;">
                            <?= date('d/m/Y H:i', strtotime($row['waktu_input'])); ?>
                        </td>
                        <td class="text-end fw-bold text-success">
                            Rp <?= number_format((float)$row['omzet'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #64748b;">
                        Belum ada laporan omzet terinput pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3" class="text-end" style="padding: 10px; font-size: 11px; text-transform: uppercase;">TOTAL KESELURUHAN OMZET KOTOR (100%):</td>
                <td class="text-end text-success" style="padding: 10px; font-size: 13px;">
                    Rp <?= number_format($totalOmzet, 0, ',', '.'); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <?php 
        $potonganGlobal = (float)($outlet['persentase_potongan'] ?? 10.00);
        $persenInvVal   = (float)($outlet['persen_bagian_investor'] ?? 50.00);
        $persenOutVal   = 100.00 - $persenInvVal;

        $pot10Pdf  = ($totalNominalPotongan > 0) ? $totalNominalPotongan : round($totalOmzet * ($potonganGlobal / 100), 2);
        $hakInvPdf = round($pot10Pdf * ($persenInvVal / 100), 2);
        $hakOutPdf = round($pot10Pdf * ($persenOutVal / 100), 2);
        $totalAkhirOutletPdf = ($totalOmzet - $pot10Pdf) + $hakOutPdf;
    ?>

    <!-- Summary Box Bagi Hasil -->
    <table class="meta-box" style="margin-bottom: 25px; border: 1px solid #cbd5e1;">
        <tr style="background-color: #f8fafc;">
            <td colspan="2" style="font-weight: bold; font-size: 12px; color: #7D0A0A; border-bottom: 1px solid #e2e8f0; padding: 8px 12px;">
                REKAPITULASI BAGI HASIL PERIODE <?= htmlspecialchars($periodeLabelStr); ?>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; border-right: 1px solid #e2e8f0;">
                <table style="width: 100%;">
                    <tr>
                        <td class="meta-label">Total Omzet Toko</td>
                        <td class="meta-value">: Rp <?= number_format($totalOmzet, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label" style="color: #dc2626;">Nominal Potongan <?= (float)$potonganGlobal; ?>%</td>
                        <td class="meta-value" style="color: #dc2626;">: Rp <?= number_format($pot10Pdf, 0, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table style="width: 100%;">
                    <tr>
                        <td class="meta-label" style="color: #16a34a;">Hak Investor (<?= (float)$persenInvVal; ?>% dari <?= (float)$potonganGlobal; ?>%)</td>
                        <td class="meta-value" style="color: #16a34a;">: Rp <?= number_format($hakInvPdf, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label" style="color: #d97706;">Hak Outlet (<?= (float)$persenOutVal; ?>% dari <?= (float)$potonganGlobal; ?>%)</td>
                        <td class="meta-value" style="color: #d97706;">: Rp <?= number_format($hakOutPdf, 0, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="background-color: #f0fdf4; border-top: 1px solid #bbf7d0;">
            <td colspan="2" style="padding: 10px 12px;">
                <table style="width: 100%;">
                    <tr>
                        <td class="meta-label" style="color: #16a34a; font-weight: bold; font-size: 11px; text-transform: uppercase;">Total Pendapatan Akhir Diterima Outlet (Omzet - Potongan + Hak Outlet):</td>
                        <td class="meta-value text-end" style="color: #16a34a; font-weight: bold; font-size: 13px;">Rp <?= number_format($totalAkhirOutletPdf, 0, ',', '.'); ?></td>
                    </tr>
                </table>
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
$dompdf->stream("Laporan_Omzet_{$cleanFilename}_{$selectedBulan}_{$selectedTahun}.pdf", ["Attachment" => 0]);
exit;
