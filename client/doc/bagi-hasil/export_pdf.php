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

$selectedProvinsi  = trim($_GET['provinsi'] ?? '');
$selectedKabupaten = trim($_GET['kabupaten'] ?? '');
$selectedKecamatan = trim($_GET['kecamatan'] ?? '');
$selectedKelurahan = trim($_GET['kelurahan'] ?? '');

$checkBulan = ($selectedBulan > 0) ? $selectedBulan : (int)date('n');
$checkTahun = ($selectedTahun > 0) ? $selectedTahun : (int)date('Y');

$rowsSummary = [];
$dailyItemsByOutlet = [];
$totOmzet = 0;
$totPotongan10 = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;

$whereConditions = ($role === 'investor') ? ["o.id_investor = {$investorId}"] : ["o.id_outlet = {$targetOutletId}"];
$selectedOutletNama = '';

if (!empty($selectedProvinsi)) {
    $safeProv = $db->real_escape_string($selectedProvinsi);
    $whereConditions[] = "mw.provinsi = '{$safeProv}'";
}
if (!empty($selectedKabupaten)) {
    $safeKab = $db->real_escape_string($selectedKabupaten);
    $whereConditions[] = "mw.kabupaten = '{$safeKab}'";
}
if (!empty($selectedKecamatan)) {
    $safeKec = $db->real_escape_string($selectedKecamatan);
    $whereConditions[] = "mw.kecamatan = '{$safeKec}'";
}
if (!empty($selectedKelurahan)) {
    $safeKel = $db->real_escape_string($selectedKelurahan);
    $whereConditions[] = "mw.kelurahan = '{$safeKel}'";
}

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

// 1. Query Rekap Per Outlet (All Outlets)
$sqlBagiHasil = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        o.persentase_potongan,
        IFNULL(o.persentase_hak_investor, 50.00) as persentase_hak_investor,
        IFNULL(SUM(l.nominal_omzet), 0) as total_omzet,
        IFNULL(SUM(l.nominal_potongan), 0) as total_potongan_db,
        IFNULL(SUM(ROUND(l.nominal_potongan * (IFNULL(l.persentase_hak_investor, IFNULL(o.persentase_hak_investor, 50.00)) / 100.0), 2)), 0) as total_hak_investor_db,
        IFNULL(SUM(ROUND(l.nominal_potongan * ((100.00 - IFNULL(l.persentase_hak_investor, IFNULL(o.persentase_hak_investor, 50.00))) / 100.0), 2)), 0) as total_hak_outlet_db
    FROM outlet o
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN laporan_omzet l ON {$joinOnClause}
    WHERE {$whereConditions[0]}
    GROUP BY o.id_outlet, o.nama_outlet, o.persentase_potongan, o.persentase_hak_investor
    ORDER BY o.nama_outlet ASC
";

$resBagiHasil = $db->query($sqlBagiHasil);

if ($resBagiHasil) {
    while ($row = $resBagiHasil->fetch_assoc()) {
        $nominal_omzet = (float)$row['total_omzet'];
        $ratePotongan  = (float)($row['persentase_potongan'] ?? 10.00);
        $rateInvestor  = (float)($row['persentase_hak_investor'] ?? 50.00);

        $potongan10  = (float)$row['total_potongan_db'];
        $hakInvestor = (float)$row['total_hak_investor_db'];
        $hakOutlet   = (float)$row['total_hak_outlet_db'];

        $row['persentase_potongan'] = $ratePotongan;
        $row['persentase_hak_investor'] = $rateInvestor;
        $row['potongan_10'] = $potongan10;
        $row['hak_investor'] = $hakInvestor;
        $row['hak_outlet'] = $hakOutlet;
        $row['total_bersih_outlet'] = ($nominal_omzet - $potongan10) + $hakOutlet;

        $totOmzet += $nominal_omzet;
        $totPotongan10 += $potongan10;
        $totHakInvestor += $hakInvestor;
        $totHakOutlet += $hakOutlet;

        $rowsSummary[] = $row;
    }
}

$countOutlet = count($rowsSummary);

// 2. Query Rincian Harian Indexed by id_outlet
$whereDailySql = implode(" AND ", $whereConditions);
$sqlDaily = "
    SELECT 
        l.id_laporan,
        l.id_outlet,
        o.nama_outlet,
        l.tanggal_omzet,
        l.nominal_omzet,
        l.persentase_potongan,
        l.nominal_potongan,
        IFNULL(l.persentase_hak_investor, IFNULL(o.persentase_hak_investor, 50.00)) as persentase_hak_investor,
        ROUND(l.nominal_potongan * (IFNULL(l.persentase_hak_investor, IFNULL(o.persentase_hak_investor, 50.00)) / 100.0), 2) as nominal_hak_investor,
        ROUND(l.nominal_potongan * ((100.00 - IFNULL(l.persentase_hak_investor, IFNULL(o.persentase_hak_investor, 50.00))) / 100.0), 2) as nominal_hak_outlet
    FROM laporan_omzet l
    JOIN outlet o ON o.id_outlet = l.id_outlet
    WHERE {$whereDailySql}
    ORDER BY l.tanggal_omzet ASC
";

$resDaily = $db->query($sqlDaily);
if ($resDaily) {
    while ($rDaily = $resDaily->fetch_assoc()) {
        $idOut = (int)$rDaily['id_outlet'];
        $dailyItemsByOutlet[$idOut][] = $rDaily;
    }
}

// HTML Template for Dompdf
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan <?= date('d-m-Y'); ?></title>
    <style>
        @page {
            margin: 7mm 9mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .page-break-before {
            page-break-before: always;
        }
        .page-break-inside-avoid {
            page-break-inside: avoid;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #7D0A0A;
            padding-bottom: 4px;
        }
        .header-title {
            color: #7D0A0A;
            font-size: 19px;
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
            font-size: 8.5px;
            color: #64748b;
            margin-top: 1px;
        }
        .meta-box {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 8px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .meta-box td {
            padding: 4px 8px;
            vertical-align: top;
            font-size: 9.5px;
            word-wrap: break-word;
        }
        .section-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #7D0A0A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 7px;
            margin-bottom: 4px;
            padding-bottom: 2px;
            border-bottom: 1.5px solid #7D0A0A;
        }
        .balance-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .balance-box {
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .balance-box-header {
            padding: 5px 8px;
            font-weight: bold;
            font-size: 9.5px;
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
            padding: 5px 8px;
            font-size: 9.5px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        /* Strict Alignment & Grid Layout for Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        .data-table th {
            background-color: #7D0A0A;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 6px 7px;
            border: 1px solid #7D0A0A;
            vertical-align: middle;
        }
        .data-table th.text-center,
        .data-table td.text-center {
            text-align: center !important;
        }
        .data-table th.text-end,
        .data-table td.text-end {
            text-align: right !important;
        }
        .data-table th.text-start,
        .data-table td.text-start {
            text-align: left !important;
        }
        .data-table td {
            padding: 5px 7px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 9.5px;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .text-start { text-align: left !important; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #dc2626; }
        .text-success { color: #16a34a; }
        .text-warning { color: #d97706; }
        .text-primary { color: #0d6efd; }
        .text-secondary { color: #64748b; }
    </style>
</head>
<body>

    <!-- HALAMAN 1: KOP HEADER & RINGKASAN KEUANGAN GLOBAL INVESTOR -->
    <table class="header-table">
        <tr>
            <td style="width: 65%;">
                <h1 class="header-title">TOKO MADURA</h1>
                <div class="header-subtitle">LAPORAN KEUANGAN</div>
                <div class="header-tagline">Ringkasan Posisi Aktiva Omzet, Potongan Skema, Distribusi Bagi Hasil &amp; Rincian Harian</div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: bottom;">
                <div style="font-size: 8.5px; color: #64748b;">Tanggal &amp; Waktu Cetak:</div>
                <div style="font-weight: bold; color: #0f172a; font-size: 10px;"><?= date('d/m/Y H:i'); ?> WIB</div>
            </td>
        </tr>
    </table>

    <!-- Profil Metadata -->
    <table class="meta-box">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 6px 10px; border-right: 1px dashed #cbd5e1;">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td style="width: 38%; color: #64748b; font-weight: bold; padding: 2px 0;">Nama Investor</td>
                        <td style="width: 4%; color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="width: 58%; color: #0f172a; font-weight: bold; padding: 2px 0;"><?= htmlspecialchars($investorNama); ?></td>
                    </tr>
                    <?php if ($selectedOutletId <= 0 && $role === 'investor') : ?>
                        <tr>
                            <td style="color: #64748b; font-weight: bold; padding: 2px 0;">Total Outlet Terdaftar</td>
                            <td style="color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                            <td style="color: #0f172a; font-weight: bold; padding: 2px 0;"><?= $countOutlet; ?> Outlet Toko</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 6px 10px;">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td style="width: 38%; color: #64748b; font-weight: bold; padding: 2px 0;">Periode Laporan</td>
                        <td style="width: 4%; color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="width: 58%; color: #0f172a; font-weight: bold; padding: 2px 0;"><?= htmlspecialchars($periodeTitleStr); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">Toko</td>
                        <td style="color: #64748b; font-weight: bold; padding: 2px 0;">:</td>
                        <td style="color: #0f172a; font-weight: bold; padding: 2px 0;"><?= htmlspecialchars($displayNamaToko); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Bagian I: Ringkasan Posisi Keuangan -->
    <div class="section-title">I. RINGKASAN KEUANGAN</div>
    <table class="balance-summary-table">
        <tr>
            <!-- SISI AKTIVA: ARUS OMZET & PENERIMAAN KAS -->
            <td style="width: 49%; vertical-align: top; padding: 0;">
                <div class="balance-box">
                    <div class="balance-box-header aktiva">
                        A. SISI AKTIVA (ARUS OMZET &amp; MODAL KULAKAN)
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
                            <td style="color: #0f172a;">MODAL KULAKAN TOKO (SISA OMZET)</td>
                            <td style="text-align: right; color: #0d6efd; font-size: 10.5px;">Rp <?= number_format($totOmzet - $totPotongan10, 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                </div>
            </td>

            <td style="width: 2%;"></td>

            <!-- SISI PASIVA: DISTRIBUSI HAK BAGI HASIL INVESTOR & OUTLET -->
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
                            <td style="color: #d97706; font-weight: bold;">(+) Total Hak Bagi Hasil Outlet</td>
                            <td style="text-align: right; font-weight: bold; color: #d97706;">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></td>
                        </tr>
                        <tr style="background-color: #f0fdf4; font-weight: bold;">
                            <td style="color: #166534;">TOTAL HAK TERDISTRIBUSI KESELURUHAN</td>
                            <td style="text-align: right; color: #166534; font-size: 10.5px;">Rp <?= number_format($totHakInvestor + $totHakOutlet, 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bagian II: Tabel Rekapitulasi Posisi Keuangan Per Outlet Toko -->
    <div class="section-title">II. TABEL REKAPITULASI KEUANGAN PER OUTLET TOKO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">NO</th>
                <th class="text-start" style="width: 18%;">NAMA OUTLET / TOKO</th>
                <th class="text-end" style="width: 13%;">AKTIVA OMZET (100%)</th>
                <th class="text-end" style="width: 12%;">POTONGAN OMZET</th>
                <th class="text-end" style="width: 12%;">HAK INVESTOR</th>
                <th class="text-end" style="width: 12%;">HAK OUTLET</th>
                <th class="text-end" style="width: 14.5%;">MODAL KULAKAN (SISA OMZET)</th>
                <th class="text-end" style="width: 14.5%;">TOTAL DITERIMA TOKO</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rowsSummary)) : ?>
                <?php $no = 1; foreach ($rowsSummary as $r) : 
                    $modalKulakan = $r['total_omzet'] - $r['potongan_10'];
                ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td class="text-start">
                            <strong><?= htmlspecialchars($r['nama_outlet']); ?></strong>
                        </td>
                        <td class="text-end fw-bold">Rp <?= number_format($r['total_omzet'], 0, ',', '.'); ?></td>
                        <td class="text-end text-danger fw-bold">Rp <?= number_format($r['potongan_10'], 0, ',', '.'); ?></td>
                        <td class="text-end text-success fw-bold">Rp <?= number_format($r['hak_investor'], 0, ',', '.'); ?></td>
                        <td class="text-end text-warning fw-bold">Rp <?= number_format($r['hak_outlet'], 0, ',', '.'); ?></td>
                        <td class="text-end text-primary fw-bold">Rp <?= number_format($modalKulakan, 0, ',', '.'); ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($r['total_bersih_outlet'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">
                        Belum ada data outlet / omzet pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($rowsSummary)) : ?>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="2" class="text-end" style="padding: 6px; font-size: 8.5px; text-transform: uppercase;">TOTAL REKAPITULASI:</td>
                    <td class="text-end" style="padding: 6px;">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></td>
                    <td class="text-end text-danger" style="padding: 6px;">Rp <?= number_format($totPotongan10, 0, ',', '.'); ?></td>
                    <td class="text-end text-success" style="padding: 6px; font-size: 9.5px;">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></td>
                    <td class="text-end text-warning" style="padding: 6px; font-size: 9.5px;">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></td>
                    <td class="text-end text-primary" style="padding: 6px; font-size: 9.5px;">Rp <?= number_format($totOmzet - $totPotongan10, 0, ',', '.'); ?></td>
                    <td class="text-end text-body-emphasis" style="padding: 6px; font-size: 9.5px;">Rp <?= number_format($totOmzet - $totPotongan10 + $totHakOutlet, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <!-- HALAMAN BARU UNTUK KELOMPOK OUTLET TRANSAKSI HARIAN (HANYA DITAMPILKAN SAAT FILTER TOKO SPESIFIK DIPILIH) -->
    <!-- Bagian III: Tabel Rincian Transaksi Harian Per Outlet Toko (Menampilkan Kolom Skema Pot. %) -->
    <?php if (!empty($rowsSummary) && ($selectedOutletId > 0 || $role === 'outlet')) : ?>
        <?php 
        $outletNum = 0;
        foreach ($rowsSummary as $rOut) : 
            $outletNum++;
            $idOut = (int)$rOut['id_outlet'];
            $items = $dailyItemsByOutlet[$idOut] ?? [];
        ?>
            <!-- Pemisah Halaman Cetak Otomatis (Page Break Before Every Store) -->
            <div class="page-break-before"></div>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 6px; border-bottom: 1.5px solid #7D0A0A; padding-bottom: 3px;">
                <tr>
                    <td style="font-size: 10.5px; font-weight: bold; color: #7D0A0A; text-transform: uppercase; vertical-align: middle;">
                        III. RINCIAN HARIAN OMZET &amp; BAGI HASIL
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <span style="background-color: #7D0A0A; color: #ffffff; padding: 3px 8px; border-radius: 3px; font-weight: bold; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                            TOKO #<?= $outletNum; ?>: <?= htmlspecialchars($rOut['nama_outlet']); ?>
                        </span>
                    </td>
                </tr>
            </table>
            
            <!-- Ringkasan Keuangan Toko Ini -->
            <table class="meta-box" style="margin-bottom: 10px; background-color: #ffffff; border: 1px solid #cbd5e1;">
                <tr>
                    <td style="width: 17%; padding: 5px 8px; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 7.5px; color: #64748b; font-weight: bold; text-transform: uppercase;">Total Omzet Toko</div>
                        <div style="font-size: 9.5px; font-weight: bold; color: #0f172a; margin-top: 2px;">Rp <?= number_format($rOut['total_omzet'], 0, ',', '.'); ?></div>
                    </td>
                    <td style="width: 17%; padding: 5px 8px; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 7.5px; color: #dc2626; font-weight: bold; text-transform: uppercase;">Potongan Skema</div>
                        <div style="font-size: 9.5px; font-weight: bold; color: #dc2626; margin-top: 2px;">Rp <?= number_format($rOut['potongan_10'], 0, ',', '.'); ?></div>
                    </td>
                    <td style="width: 16%; padding: 5px 8px; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 7.5px; color: #16a34a; font-weight: bold; text-transform: uppercase;">Hak Investor</div>
                        <div style="font-size: 9.5px; font-weight: bold; color: #16a34a; margin-top: 2px;">Rp <?= number_format($rOut['hak_investor'], 0, ',', '.'); ?></div>
                    </td>
                    <td style="width: 16%; padding: 5px 8px; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 7.5px; color: #d97706; font-weight: bold; text-transform: uppercase;">Hak Outlet</div>
                        <div style="font-size: 9.5px; font-weight: bold; color: #d97706; margin-top: 2px;">Rp <?= number_format($rOut['hak_outlet'], 0, ',', '.'); ?></div>
                    </td>
                    <td style="width: 17%; padding: 5px 8px; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 7.5px; color: #0d6efd; font-weight: bold; text-transform: uppercase;">Modal Kulakan</div>
                        <div style="font-size: 9.5px; font-weight: bold; color: #0d6efd; margin-top: 2px;">Rp <?= number_format($rOut['total_omzet'] - $rOut['potongan_10'], 0, ',', '.'); ?></div>
                    </td>
                    <td style="width: 17%; padding: 5px 8px;">
                        <div style="font-size: 7.5px; color: #334155; font-weight: bold; text-transform: uppercase;">Total Diterima Toko</div>
                        <div style="font-size: 9.5px; font-weight: bold; color: #0f172a; margin-top: 2px;">Rp <?= number_format($rOut['total_bersih_outlet'], 0, ',', '.'); ?></div>
                    </td>
                </tr>
            </table>

            <!-- Tabel Rincian Transaksi Harian Omzet Toko Ini (11 Kolom Rapi Termasuk Modal Kulakan) -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 3%;">NO</th>
                        <th class="text-center" style="width: 9%;">TANGGAL OMZET</th>
                        <th class="text-end" style="width: 11%;">OMZET KOTOR</th>
                        <th class="text-center" style="width: 5%;">POT. (%)</th>
                        <th class="text-end" style="width: 10%;">NOMINAL POT.</th>
                        <th class="text-center" style="width: 5%;">INV. (%)</th>
                        <th class="text-end" style="width: 10%;">HAK INVESTOR</th>
                        <th class="text-center" style="width: 5%;">OUT. (%)</th>
                        <th class="text-end" style="width: 10%;">HAK OUTLET</th>
                        <th class="text-end" style="width: 11%;">MODAL KULAKAN</th>
                        <th class="text-end" style="width: 11%;">TOTAL DITERIMA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)) : ?>
                        <?php 
                        $noD = 1; 
                        $subOmzet = 0; $subPot = 0; $subInv = 0; $subOut = 0;
                        foreach ($items as $item) :
                            $nOmzet = (float)$item['nominal_omzet'];
                            $nPot   = (float)$item['nominal_potongan'];
                            $nInv   = (float)$item['nominal_hak_investor'];
                            $nOut   = (float)$item['nominal_hak_outlet'];
                            $nKulakan = $nOmzet - $nPot;
                            $nBersih = $nKulakan + $nOut;

                            $subOmzet += $nOmzet;
                            $subPot   += $nPot;
                            $subInv   += $nInv;
                            $subOut   += $nOut;
                            
                            $tglFmt = date('d/m/Y', strtotime($item['tanggal_omzet']));
                            $pctPot = (float)$item['persentase_potongan'];
                            $pctInv = (float)($item['persentase_hak_investor'] ?? 50.00);
                            $pctOut = 100.00 - $pctInv;
                            $fmtInv = (floor($pctInv) == $pctInv) ? number_format($pctInv, 0) . '%' : number_format($pctInv, 2) . '%';
                            $fmtOut = (floor($pctOut) == $pctOut) ? number_format($pctOut, 0) . '%' : number_format($pctOut, 2) . '%';
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $noD++; ?></td>
                                <td class="text-center fw-bold"><?= $tglFmt; ?></td>
                                <td class="text-end fw-bold">Rp <?= number_format($nOmzet, 0, ',', '.'); ?></td>
                                <td class="text-center fw-bold text-secondary"><?= number_format($pctPot, 2); ?>%</td>
                                <td class="text-end text-danger fw-bold">Rp <?= number_format($nPot, 0, ',', '.'); ?></td>
                                <td class="text-center fw-bold text-success" style="font-size: 8px;"><?= $fmtInv; ?></td>
                                <td class="text-end text-success fw-bold">Rp <?= number_format($nInv, 0, ',', '.'); ?></td>
                                <td class="text-center fw-bold text-warning" style="font-size: 8px;"><?= $fmtOut; ?></td>
                                <td class="text-end text-warning fw-bold">Rp <?= number_format($nOut, 0, ',', '.'); ?></td>
                                <td class="text-end text-primary fw-bold">Rp <?= number_format($nKulakan, 0, ',', '.'); ?></td>
                                <td class="text-end fw-bold">Rp <?= number_format($nBersih, 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="11" class="text-center" style="padding: 15px; color: #64748b;">
                                Belum ada rincian transaksi harian omzet untuk toko <strong><?= htmlspecialchars($rOut['nama_outlet']); ?></strong> pada periode ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($items)) : ?>
                    <tfoot>
                        <tr style="background-color: #f1f5f9; font-weight: bold;">
                            <td colspan="2" class="text-end" style="padding: 6px; font-size: 8px; text-transform: uppercase;">SUBTOTAL:</td>
                            <td class="text-end" style="padding: 6px;">Rp <?= number_format($subOmzet, 0, ',', '.'); ?></td>
                            <td class="text-center" style="padding: 6px;">-</td>
                            <td class="text-end text-danger" style="padding: 6px;">Rp <?= number_format($subPot, 0, ',', '.'); ?></td>
                            <td class="text-center" style="padding: 6px;">-</td>
                            <td class="text-end text-success" style="padding: 6px; font-size: 9px;">Rp <?= number_format($subInv, 0, ',', '.'); ?></td>
                            <td class="text-center" style="padding: 6px;">-</td>
                            <td class="text-end text-warning" style="padding: 6px; font-size: 9px;">Rp <?= number_format($subOut, 0, ',', '.'); ?></td>
                            <td class="text-end text-primary" style="padding: 6px; font-size: 9px;">Rp <?= number_format($subOmzet - $subPot, 0, ',', '.'); ?></td>
                            <td class="text-end" style="padding: 6px; font-size: 9px;">Rp <?= number_format($subOmzet - $subPot + $subOut, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Bagian IV: Catatan Keuangan & Pengesahan Lembar Laporan (Page Break Avoid) -->
    <table class="page-break-inside-avoid" style="width: 100%; border-collapse: collapse; margin-top: 8px;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding-right: 15px;">
                <div style="border: 1px solid #cbd5e1; background-color: #f8fafc; border-radius: 6px; padding: 8px 10px;">
                    <div style="font-weight: bold; font-size: 8.5px; color: #334155; margin-bottom: 3px; text-transform: uppercase;">CATATAN KEUANGAN &amp; AUDIT HARIAN:</div>
                    <ul style="margin: 0; padding-left: 12px; font-size: 8px; color: #64748b; line-height: 1.35;">
                        <li><strong>Modal Kulakan (Sisa Omzet):</strong> Dana pokok operasional toko dari omzet kotor setelah dipotong skema, dialokasikan untuk kulakan dan pembelian bahan/stok dagangan kembali.</li>
                        <li><strong>Total Diterima Toko:</strong> Total penerimaan dana yang dipegang pihak toko, akumulasi dari Modal Kulakan ditambah Hak Bagi Hasil milik outlet.</li>
                        <li><strong>Hak Investor:</strong> Bagian hasil bersih milik investor sesuai kesepakatan persentase bagi hasil.</li>
                    </ul>
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: center;">
               
                <div style="font-weight: bold; font-size: 9px; color: #0f172a; margin-top: 1px;">MANAJEMEN TOKO MADURA</div>
                <div style="height: 35px;"></div>
                <div style="font-weight: bold; font-size: 9px; color: #7D0A0A; text-decoration: underline;"><?= htmlspecialchars($investorNama); ?></div>
                <div style="font-size: 8px; color: #64748b;">Pihak Investor</div>
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

$pdfFilename = "Laporan Keuangan " . date('d-m-Y') . ".pdf";
$dompdf->stream($pdfFilename, ["Attachment" => 0]);
exit;
