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
$persenInvestor = 50.00;
$targetOutletId = 0;
$investorNama = $user['nama_lengkap'] ?? $user['username'] ?? 'Investor';

if ($role === 'investor') {
    $resInv = $db->query("SELECT i.id_investor, u.nama_lengkap FROM investor i JOIN users u ON u.id_users = i.id_users WHERE i.id_users = {$userId} LIMIT 1");
    if ($resInv && $resInv->num_rows > 0) {
        $rowInv = $resInv->fetch_assoc();
        $investorId = (int)$rowInv['id_investor'];
        if (!empty($rowInv['nama_lengkap'])) {
            $investorNama = $rowInv['nama_lengkap'];
        }
    }
} else {
    $resOut = $db->query("SELECT o.id_outlet, o.id_investor, o.persentase_potongan, IFNULL(o.persentase_hak_investor, 50.00) as persentase_hak_investor, u_inv.nama_lengkap as nama_investor FROM outlet o LEFT JOIN investor inv ON o.id_investor = inv.id_investor LEFT JOIN users u_inv ON inv.id_users = u_inv.id_users WHERE o.id_users = {$userId} LIMIT 1");
    if ($resOut && $resOut->num_rows > 0) {
        $rowOut = $resOut->fetch_assoc();
        $investorId = (int)$rowOut['id_investor'];
        $persenInvestor = (float)($rowOut['persentase_hak_investor'] ?? 50.00);
        $targetOutletId = (int)$rowOut['id_outlet'];
        if (!empty($rowOut['nama_investor'])) {
            $investorNama = $rowOut['nama_investor'];
        }
    }
}

// Filter Logic (Outlet, Rentang Tanggal, Bulan, Tahun)
$selectedOutletId   = isset($_GET['outlet_id']) ? (int)$_GET['outlet_id'] : (isset($_GET['id_outlet']) ? (int)$_GET['id_outlet'] : (isset($_GET['outlet']) ? (int)$_GET['outlet'] : 0));
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$checkBulan = ($selectedBulan > 0) ? $selectedBulan : (int)date('n');
$checkTahun = ($selectedTahun > 0) ? $selectedTahun : (int)date('Y');

$rows = [];
$totOmzet = 0;
$totPotongan10 = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;

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
$displayNamaToko = (!empty($selectedOutletNama) && $selectedOutletId > 0) ? $selectedOutletNama : "Semua Toko Terdaftar";

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
        $ratePotongan  = (float)($row['persentase_potongan'] ?? 10.00);
        $rateInvestor  = (float)($row['persentase_hak_investor'] ?? 50.00);

        $countRates = (int)($row['count_distinct_rates'] ?? 0);
        $minRate    = (float)($row['min_rate'] ?? $ratePotongan);
        $maxRate    = (float)($row['max_rate'] ?? $ratePotongan);

        if ($countRates > 1 && $minRate !== $maxRate) {
            $displayRate = "Variatif (" . $minRate . "% - " . $maxRate . "%)";
        } elseif ($minRate > 0) {
            $displayRate = $minRate . "%";
        } else {
            $displayRate = $ratePotongan . "%";
        }

        $potongan10  = (float)$row['total_potongan_db'];
        $hakInvestor = (float)$row['total_hak_investor_db'];
        $hakOutlet   = (float)$row['total_hak_outlet_db'];

        $row['persentase_potongan'] = $ratePotongan;
        $row['display_rate'] = $displayRate;
        $row['persentase_hak_investor'] = $rateInvestor;
        $row['potongan_10'] = $potongan10;
        $row['hak_investor'] = $hakInvestor;
        $row['hak_outlet'] = $hakOutlet;
        $row['total_bersih_outlet'] = ($nominal_omzet - $potongan10) + $hakOutlet;

        $totOmzet += $nominal_omzet;
        $totPotongan10 += $potongan10;
        $totHakInvestor += $hakInvestor;
        $totHakOutlet += $hakOutlet;

        $rows[] = $row;
    }
}

$countOutlet = count($rows);

// HTML Template for Dompdf (Data Neraca Sederhana)
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Neraca Sederhana Investor - <?= htmlspecialchars($periodeTitleStr); ?></title>
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
            margin-bottom: 15px;
            border-bottom: 2px solid #7D0A0A;
            padding-bottom: 8px;
        }
        .header-title {
            color: #7D0A0A;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .header-tagline {
            font-size: 9px;
            color: #64748b;
            margin-top: 1px;
        }
        .meta-box {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .meta-box td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10px;
            word-wrap: break-word;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #7D0A0A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }
        .balance-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .balance-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .balance-box-header {
            padding: 6px 10px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            color: #ffffff;
        }
        .balance-box-header.aktiva {
            background-color: #7D0A0A;
        }
        .balance-box-header.pasiva {
            background-color: #16a34a;
        }
        .balance-row-table {
            width: 100%;
            border-collapse: collapse;
        }
        .balance-row-table td {
            padding: 7px 10px;
            font-size: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 9.5px;
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
        .text-primary { color: #0d6efd; }
    </style>
</head>
<body>

    <!-- Kop Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 65%;">
                <h1 class="header-title">TOKO MADURA</h1>
                <div class="header-subtitle">LAPORAN NERACA KEUANGAN SEDERHANA &amp; HAK INVESTOR</div>
                <div class="header-tagline">Ikhtisar Posisi Aktiva Omzet, Potongan Skema, dan Distribusi Bagi Hasil</div>
            </td>
            <td style="width: 35%; text-align: right;">
                <div style="font-size: 8.5px; color: #64748b;">No. Dokumen Neraca:</div>
                <div style="font-weight: bold; color: #7D0A0A; font-size: 11px;">FIN-NERACA-<?= date('Ymd'); ?>/<?= sprintf('%03d', $investorId); ?></div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 4px;">Tanggal &amp; Waktu Cetak:</div>
                <div style="font-weight: bold; color: #0f172a; font-size: 10px;"><?= date('d/m/Y H:i'); ?> WIB</div>
            </td>
        </tr>
    </table>

    <!-- Profil Metadata Neraca -->
    <table class="meta-box">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 8px 12px; border-right: 1px dashed #cbd5e1;">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td style="width: 38%; color: #64748b; font-weight: bold; padding: 2px 0;">Nama Investor</td>
                        <td style="width: 4%; color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="width: 58%; color: #0f172a; font-weight: bold; padding: 2px 0;"><?= htmlspecialchars($investorNama); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">Akses Sistem</td>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="color: #0f172a; font-weight: bold; padding: 2px 0;"><?= strtoupper($role); ?> PANEL</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">Total Outlet Terdaftar</td>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="color: #0f172a; font-weight: bold; padding: 2px 0;"><?= $countOutlet; ?> Outlet Toko</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 8px 12px;">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td style="width: 38%; color: #64748b; font-weight: bold; padding: 2px 0;">Periode Laporan</td>
                        <td style="width: 4%; color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="width: 58%; color: #0f172a; font-weight: bold; padding: 2px 0;"><?= htmlspecialchars($periodeTitleStr); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">Cakupan Toko</td>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="color: #0f172a; font-weight: bold; padding: 2px 0;"><?= htmlspecialchars($displayNamaToko); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">Status Audit Data</td>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="color: #16a34a; font-weight: bold; padding: 2px 0;">TERVERIFIKASI SISTEM</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Bagian I: Ikhtisar Posisi Neraca Keuangan (2-Column Balance Sheet Table) -->
    <div class="section-title">I. IKHTISAR POSISI NERACA KEUANGAN (FINANCIAL BALANCE SHEET SUMMARY)</div>
    <table class="balance-summary-table">
        <tr>
            <!-- SISI AKTIVA: ARUS OMZET & PENERIMAAN KAS -->
            <td style="width: 49%; vertical-align: top; padding: 0;">
                <div class="balance-box">
                    <div class="balance-box-header aktiva">
                        A. SISI AKTIVA (PEMASUKAN &amp; ARUS OMZET)
                    </div>
                    <table class="balance-row-table">
                        <tr>
                            <td style="color: #475569;">Total Omzet Kotor Penjualan (100%)</td>
                            <td style="text-align: right; font-weight: bold; color: #0f172a;">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></td>
                        </tr>
                        <tr style="background-color: #fafafa;">
                            <td style="color: #dc2626;">(-) Total Potongan Alokasi Skema Harian</td>
                            <td style="text-align: right; font-weight: bold; color: #dc2626;">Rp <?= number_format($totPotongan10, 0, ',', '.'); ?></td>
                        </tr>
                        <tr style="background-color: #f8fafc; font-weight: bold;">
                            <td style="color: #0f172a;">TOTAL ARUS KAS BERSIH TOKO</td>
                            <td style="text-align: right; color: #0d6efd; font-size: 11px;">Rp <?= number_format($totOmzet - $totPotongan10, 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                </div>
            </td>

            <td style="width: 2%;"></td>

            <!-- SISI PASIVA: DISTRIBUSI BAGI HASIL INVESTOR & OUTLET -->
            <td style="width: 49%; vertical-align: top; padding: 0;">
                <div class="balance-box" style="border-color: #16a34a;">
                    <div class="balance-box-header pasiva">
                        B. SISI PASIVA (DISTRIBUSI HAK BAGI HASIL)
                    </div>
                    <table class="balance-row-table">
                        <tr>
                            <td style="color: #16a34a; font-weight: bold;">(+) Total Hak Bagi Hasil Investor</td>
                            <td style="text-align: right; font-weight: bold; color: #16a34a;">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></td>
                        </tr>
                        <tr style="background-color: #fafafa;">
                            <td style="color: #d97706; font-weight: bold;">(+) Total Hak Bagi Hasil Outlet (Pengelola)</td>
                            <td style="text-align: right; font-weight: bold; color: #d97706;">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></td>
                        </tr>
                        <tr style="background-color: #f0fdf4; font-weight: bold;">
                            <td style="color: #166534;">TOTAL HAK TERDISTRIBUSI KESELURUHAN</td>
                            <td style="text-align: right; color: #166534; font-size: 11px;">Rp <?= number_format($totHakInvestor + $totHakOutlet, 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bagian II: Tabel Rincian Posisi Neraca Per Outlet Toko -->
    <div class="section-title">II. TABEL RINCIAN POSISI NERACA PER OUTLET TOKO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 150px;">Nama Outlet / Toko</th>
                <th class="text-end">Aktiva Omzet (100%)</th>
                <th class="text-center" style="width: 65px;">Skema Pot.</th>
                <th class="text-end">Potongan Omzet</th>
                <th class="text-end">Hak Investor</th>
                <th class="text-end">Hak Outlet</th>
                <th class="text-end">Saldo Bersih Toko</th>
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
                        <td class="text-center fw-bold text-secondary"><?= htmlspecialchars($r['display_rate']); ?></td>
                        <td class="text-end text-danger fw-bold">Rp <?= number_format($r['potongan_10'], 0, ',', '.'); ?></td>
                        <td class="text-end text-success fw-bold">Rp <?= number_format($r['hak_investor'], 0, ',', '.'); ?></td>
                        <td class="text-end text-warning fw-bold">Rp <?= number_format($r['hak_outlet'], 0, ',', '.'); ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($r['total_bersih_outlet'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        Belum ada data outlet / omzet pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($rows)) : ?>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="2" class="text-end" style="padding: 8px; font-size: 9.5px; text-transform: uppercase;">TOTAL KESELURUHAN:</td>
                    <td class="text-end" style="padding: 8px;">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></td>
                    <td class="text-center" style="padding: 8px;">-</td>
                    <td class="text-end text-danger" style="padding: 8px;">Rp <?= number_format($totPotongan10, 0, ',', '.'); ?></td>
                    <td class="text-end text-success" style="padding: 8px; font-size: 10.5px;">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></td>
                    <td class="text-end text-warning" style="padding: 8px; font-size: 10.5px;">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></td>
                    <td class="text-end text-primary" style="padding: 8px; font-size: 10.5px;">Rp <?= number_format($totOmzet - $totPotongan10 + $totHakOutlet, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <!-- Bagian III: Catatan Keuangan & Pengesahan Lembar Neraca -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding-right: 15px;">
                <div style="border: 1px solid #cbd5e1; background-color: #f8fafc; border-radius: 6px; padding: 8px 10px;">
                    <div style="font-weight: bold; font-size: 9px; color: #334155; margin-bottom: 3px; text-transform: uppercase;">CATATAN KEUANGAN &amp; AUDIT NERACA:</div>
                    <ul style="margin: 0; padding-left: 12px; font-size: 8px; color: #64748b; line-height: 1.35;">
                        <li>Laporan disajikan sebagai Neraca Keuangan Sederhana untuk memperhitungkan realisasi omzet kotor, potongan alokasi skema, dan porsi hak investor secara akurat.</li>
                        <li>Sisi Aktiva mencatat penerimaan omzet kotor penjualan outlet, sedangkan Sisi Pasiva mencatat distribusi hak bagi hasil investor dan pengelola toko.</li>
                        <li>Data neraca ini ditarik secara otomatis dari sistem Toko Madura yang telah diverifikasi dan siap digunakan sebagai bukti rekapitulasi keuangan.</li>
                    </ul>
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: center;">
                <div style="font-size: 9px; color: #64748b;">Disahkan &amp; Diverifikasi Oleh,</div>
                <div style="font-weight: bold; font-size: 9.5px; color: #0f172a; margin-top: 1px;">MANAJEMEN TOKO MADURA</div>
                <div style="height: 38px;"></div>
                <div style="font-weight: bold; font-size: 9.5px; color: #7D0A0A; text-decoration: underline;"><?= htmlspecialchars($investorNama); ?></div>
                <div style="font-size: 8px; color: #64748b;">Pihak Investor / Pemilik Modal</div>
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

$dompdf->stream("Laporan_Neraca_Sederhana_Investor_{$checkBulan}_{$checkTahun}.pdf", ["Attachment" => 0]);
exit;
