<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);
$role = strtolower($user['role'] ?? 'investor');

// Array nama bulan Bahasa Indonesia
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$investorId = 0;
$persenInvestor = 50.00; // Default 50%
$targetOutletId = 0;

if ($role === 'investor') {
    // Get Investor ID for logged in investor
    $resInv = $db->query("SELECT id_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
    if ($resInv && $resInv->num_rows > 0) {
        $rowInv = $resInv->fetch_assoc();
        $investorId = (int)$rowInv['id_investor'];
        $persenInvestor = 50.00;
    }
} else {
    // Logged in user is Outlet
    $resOut = $db->query("SELECT o.id_outlet, o.id_investor, IFNULL(o.persentase_hak_investor, 50.00) as persentase_hak_investor FROM outlet o WHERE o.id_users = {$userId} LIMIT 1");
    if ($resOut && $resOut->num_rows > 0) {
        $rowOut = $resOut->fetch_assoc();
        $investorId = (int)$rowOut['id_investor'];
        $persenInvestor = (float)($rowOut['persentase_hak_investor'] ?? 50.00);
        $targetOutletId = (int)$rowOut['id_outlet'];
    }
}
$persenOutletBagiHasil = 100.00 - $persenInvestor; // 50%

// Get outlet deduction percentage dynamically from outlet table
$potonganGlobal = 10.00;
if (isset($outletsList) && is_array($outletsList) && !empty($outletsList[0]['persentase_potongan'])) {
    $potonganGlobal = (float)$outletsList[0]['persentase_potongan'];
}

$totOmzet = 0;
$totPotongan10 = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;
$totBersihOutlet = 0;

$outletsBreakdown = [];

// Filter Logic (Outlet, Rentang Tanggal, Bulan, Tahun)
$selectedOutletId   = isset($_GET['outlet_id']) ? (int)$_GET['outlet_id'] : (isset($_GET['id_outlet']) ? (int)$_GET['id_outlet'] : (isset($_GET['outlet']) ? (int)$_GET['outlet'] : 0));
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;


// Fetch list of outlets belonging to logged in investor
$investorOutlets = [];
if ($role === 'investor' && $investorId > 0) {
    $resOuts = $db->query("SELECT o.id_outlet, o.nama_outlet FROM outlet o JOIN users u ON u.id_users = o.id_users WHERE o.id_investor = {$investorId} ORDER BY o.nama_outlet ASC");
    if ($resOuts) {
        while ($oRow = $resOuts->fetch_assoc()) {
            $investorOutlets[] = $oRow;
        }
    }
}

// Determine last day of selected month/year to check if deduction is active
$checkBulan = ($selectedBulan > 0) ? $selectedBulan : (int)date('n');
$checkTahun = ($selectedTahun > 0) ? $selectedTahun : (int)date('Y');
$lastDayStr = date('Y-m-t', strtotime("{$checkTahun}-{$checkBulan}-01"));
$todayStr = date('Y-m-d');
$isMonthEnded = ($todayStr >= $lastDayStr);

$totOmzet = 0;
$totPotongan = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;
$hasAnyLastDayDone = false;

// Fetch distinct years available in database
$availableYears = [];
$whereYearSql = ($role === 'investor') ? "o.id_investor = {$investorId}" : "o.id_outlet = {$targetOutletId}";
$resYears = $db->query("SELECT DISTINCT YEAR(l.tanggal_omzet) as y_periode FROM laporan_omzet l JOIN outlet o ON l.id_outlet = o.id_outlet WHERE {$whereYearSql} ORDER BY y_periode DESC");
if ($resYears) {
    while ($yRow = $resYears->fetch_assoc()) {
        $availableYears[] = (int)$yRow['y_periode'];
    }
}
if (!in_array((int)date('Y'), $availableYears)) {
    array_unshift($availableYears, (int)date('Y'));
}

$whereConditions = ($role === 'investor') ? ["o.id_investor = {$investorId}"] : ["o.id_outlet = {$targetOutletId}"];
$selectedOutletNama = '';

if ($selectedOutletId > 0 && $role === 'investor') {
    $whereConditions[0] = "o.id_outlet = {$selectedOutletId}";
    $resOneOut = $db->query("SELECT o.nama_outlet FROM outlet o JOIN users u ON u.id_users = o.id_users WHERE o.id_outlet = {$selectedOutletId} LIMIT 1");
    if ($resOneOut && $resOneOut->num_rows > 0) {
        $selectedOutletNama = $resOneOut->fetch_assoc()['nama_outlet'];
    }
} elseif ($role === 'outlet' && $targetOutletId > 0) {
    $resOneOut = $db->query("SELECT o.nama_outlet FROM outlet o JOIN users u ON u.id_users = o.id_users WHERE o.id_outlet = {$targetOutletId} LIMIT 1");
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
$periodeLabelStr = $periodeTitleStr;
if (!empty($selectedOutletNama) && $selectedOutletId > 0) {
    $periodeLabelStr .= " - " . $selectedOutletNama;
}

$laporanJoinConds = array_filter($whereConditions, fn($c) => strpos($c, 'o.') === false);
$joinOnClause = "o.id_outlet = l.id_outlet";
if (!empty($laporanJoinConds)) {
    $joinOnClause .= " AND " . implode(" AND ", $laporanJoinConds);
}

// Fetch breakdown per outlet
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
    JOIN users u ON u.id_users = o.id_users
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

        // Total Pendapatan Bersih Outlet (Omzet - Hak Investor)
        $totalBersihOutlet = ($nominal_omzet - $potongan10) + $hakOutlet;

        $row['persentase_potongan'] = $ratePotongan;
        $row['display_rate'] = $displayRate;
        $row['persentase_hak_investor'] = $rateInvestor;
        $row['is_last_day_done'] = true;
        $row['potongan_10'] = $potongan10;
        $row['hak_investor'] = $hakInvestor;
        $row['hak_outlet'] = $hakOutlet;
        $row['total_bersih_outlet'] = $totalBersihOutlet;

        $totOmzet += $nominal_omzet;
        $totPotongan10 += $potongan10;
        $totHakInvestor += $hakInvestor;
        $totHakOutlet += $hakOutlet;

        $rows[] = $row;
    }
}

$countOutlet = count($rows);
?>

<style>
/* Modern Glassmorphism & Theme Adaptive Styling for Bagi Hasil */
.hero-bagi-hasil-banner {
    background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);
    border-radius: 18px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(125, 10, 10, 0.3);
}

.box-stat-bagi-hasil {
    border-radius: 14px;
    padding: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid var(--bs-border-color, #e2e8f0);
}

.box-stat-omzet {
    background: rgba(13, 110, 253, 0.06);
    border-color: rgba(13, 110, 253, 0.2);
}

.box-stat-potongan {
    background: rgba(220, 53, 69, 0.06);
    border-color: rgba(220, 53, 69, 0.2);
}

.box-stat-investor {
    background: rgba(25, 135, 84, 0.08);
    border-color: rgba(25, 135, 84, 0.25);
}

.box-stat-outlet {
    background: rgba(255, 193, 7, 0.08);
    border-color: rgba(255, 193, 7, 0.3);
}

.box-stat-belanja {
    background: rgba(125, 10, 10, 0.08);
    border-color: rgba(125, 10, 10, 0.28);
}

.text-brown {
    color: #7D0A0A !important;
}

.bg-brown {
    background-color: #7D0A0A !important;
}

.card-stat-title-full {
    font-size: 12px;
    line-height: 1.35;
    font-weight: 700;
    letter-spacing: 0.3px;
    display: block;
    word-break: break-word;
    white-space: normal;
}

.stat-icon-circle-sm {
    width: 36px;
    height: 36px;
    font-size: 14px;
}

/* Custom Pill Filter Bar */
.filter-pill-container {
    background-color: var(--bs-body-bg, #ffffff);
    border: 1px solid var(--bs-border-color, #dee2e6);
    border-radius: 50rem;
    padding: 6px 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.filter-pill-container select {
    border: none !important;
    background: transparent !important;
    font-weight: 700 !important;
    color: var(--bs-body-color) !important;
    font-size: 12px;
    padding-left: 2px;
    padding-right: 18px;
    cursor: pointer;
    box-shadow: none !important;
}

@media (min-width: 768px) {
    .card-stat-title-full {
        font-size: 13px;
    }
    .stat-icon-circle-sm {
        width: 42px;
        height: 42px;
        font-size: 16px;
    }
    .filter-pill-container select {
        font-size: 13px;
        padding-right: 22px;
    }
}

@media (max-width: 575.98px) {
    .box-stat-bagi-hasil {
        padding: 12px;
    }
    .card-stat-title-full {
        font-size: 11px;
    }
    .filter-pill-container {
        width: 100%;
        justify-content: space-between;
    }
}
</style>

<div class="main-content-inner pt-0 mt-0">
    <!-- Header Title & Action Bar Card (Sleek & Balanced) -->
    <div class="card border border-body-subtle shadow-sm mb-3 mb-md-4" style="border-radius: 16px;">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <!-- Title Group with Icon & Subtitle -->
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 bg-danger-subtle text-danger p-2 p-md-3 flex-shrink-0" style="width: 46px; height: 46px;">
                        <i class="fa-solid fa-vault fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-extrabold text-body-emphasis mb-1 fs-5 fs-md-4" style="letter-spacing: -0.3px;">
                            Laporan Bagi Hasil <?= ($role === 'investor') ? 'Investor' : 'Toko'; ?>
                        </h3>
                        <p class="text-body-secondary small mb-0">Rekapitulasi komisi investor dan hak outlet per periode</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clean Compact Hero Monitoring Card -->
    <div class="card border-0 hero-bagi-hasil-banner p-3 p-md-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7 col-12">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-chart-pie me-1"></i> <?= strtoupper($role); ?> DASHBOARD
                    </span>
                </div>
                <h2 class="fw-bold text-white mb-1 fs-3 fs-md-2">Bagi Hasil Periode <?= htmlspecialchars($periodeLabelStr); ?></h2>
                <?php if (!empty($selectedOutletNama)) : ?>
                    <p class="text-white-50 small mb-0"><i class="fa-solid fa-circle-info me-1 text-warning"></i> Rekapitulasi bagi hasil khusus untuk toko <strong class="text-white"><?= htmlspecialchars($selectedOutletNama); ?></strong></p>
                <?php else : ?>
                    <p class="text-white-50 small mb-0">Total akumulasi komisi investor dan hak bersih seluruh outlet terdaftar</p>
                <?php endif; ?>
            </div>
            <div class="col-lg-5 col-12 text-lg-end">
                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 text-center text-lg-end">
                    <div class="text-white-50 small fw-semibold">Total Hak Bagi Hasil Investor</div>
                    <div class="fs-2 fw-extrabold text-warning">
                        Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?>
                    </div>
                    <div class="text-white-50 small">
                        <?php if ($hasAnyLastDayDone) : ?>
                           
                        <?php else : ?>
                            <i class="fa-light fa-clock me-1 text-warning"></i>Pendataan berjalan (Menunggu Tgl akhir bulan <?= $daysInMonth; ?>)
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Summary Metric Cards (Mobile Readable - No Truncate) -->
    <div class="row g-2 g-md-3 mb-4 row-cols-2 row-cols-md-3 row-cols-xl-5">
        <!-- 1. Total Omzet Reported -->
        <div class="col">
            <div class="box-stat-bagi-hasil box-stat-omzet h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-primary text-uppercase card-stat-title-full">Total Omzet (100%)</span>
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 stat-icon-circle-sm">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
                <div>
                    <div class="fs-6 fs-md-4 fw-extrabold text-primary mb-1">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></div>
                    <small class="text-body-secondary micro-text d-block">Total akumulasi omzet kotor</small>
                </div>
            </div>
        </div>

        <!-- 2. Potongan Dynamic -->
        <div class="col">
            <div class="box-stat-bagi-hasil box-stat-potongan h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-danger text-uppercase card-stat-title-full">Potongan Skema</span>
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0 stat-icon-circle-sm">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                </div>
                <div>
                    <div class="fs-6 fs-md-4 fw-extrabold text-danger mb-1">
                        <?php if ($hasAnyLastDayDone || $selectedBulan === 0) : ?>
                            Rp <?= number_format($totPotongan10, 0, ',', '.'); ?>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </div>
                    <small class="text-body-secondary micro-text d-block">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Total potongan komisi' : 'Berlaku di tgl ' . $daysInMonth; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- 3. Hak Investor -->
        <div class="col">
            <div class="box-stat-bagi-hasil box-stat-investor h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-success text-uppercase card-stat-title-full">Hak Investor</span>
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0 stat-icon-circle-sm">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <div>
                    <div class="fs-6 fs-md-4 fw-extrabold text-success mb-1">
                        <?php if ($hasAnyLastDayDone || $selectedBulan === 0) : ?>
                            Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </div>
                    <small class="text-success micro-text d-block">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? '<i class="fa-solid fa-arrow-trend-up me-1"></i>Hak bersih investor' : 'Dihitung tgl akhir bulan'; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- 4. Hak Outlet -->
        <div class="col">
            <div class="box-stat-bagi-hasil box-stat-outlet h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-body-emphasis text-uppercase card-stat-title-full">Hak Outlet</span>
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0 stat-icon-circle-sm">
                        <i class="fa-solid fa-store"></i>
                    </div>
                </div>
                <div>
                    <div class="fs-6 fs-md-4 fw-extrabold text-warning mb-1">
                        <?php if ($hasAnyLastDayDone || $selectedBulan === 0) : ?>
                            Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </div>
                    <small class="text-body-secondary micro-text d-block">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Bagian milik outlet' : 'Dihitung tgl akhir bulan'; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- 5. Modal Belanja Sisa Omzet -->
        <div class="col">
            <div class="box-stat-bagi-hasil box-stat-belanja h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-brown text-uppercase card-stat-title-full">Modal Belanja</span>
                    <div class="rounded-circle bg-brown text-white d-flex align-items-center justify-content-center flex-shrink-0 stat-icon-circle-sm">
                        <i class="fa-solid fa-cart-flatbed-boxes"></i>
                    </div>
                </div>
                <div>
                    <div class="fs-6 fs-md-4 fw-extrabold text-brown mb-1">
                        <?php if ($hasAnyLastDayDone || $selectedBulan === 0) : ?>
                            Rp <?= number_format($totOmzet - $totPotongan10, 0, ',', '.'); ?>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </div>
                    <small class="text-body-secondary micro-text d-block">
                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Sisa omzet belanja stok' : 'Dihitung tgl akhir bulan'; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown Table Per Outlet (Sleek Theme-Adaptive Card Container) -->
    <div class="card border border-body-subtle shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-body-emphasis mb-0 fs-6">
                    <i class="fa-solid fa-list-check me-2 text-danger"></i>Rincian Pembagian Hak Per Outlet (<?= htmlspecialchars($periodeLabelStr); ?>)
                </h5>
                <p class="text-body-secondary small mb-0">Rincian omzet, nominal potongan investor, modal belanja, serta hak investor &amp; outlet</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Tombol Filter Utama -->
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterBagiHasil">
                    <i class="fa-solid fa-filter me-1"></i> Filter Data
                </button>
                <!-- Tombol Cetak PDF Bagi Hasil & Data Keuangan Sederhana -->
                <a href="<?= SystemInfo::app('CLIENT_URL'); ?>/doc/bagi-hasil/export_pdf.php?outlet_id=<?= $selectedOutletId; ?>&tgl_mulai=<?= urlencode($selectedTglMulai); ?>&tgl_selesai=<?= urlencode($selectedTglSelesai); ?>&bulan=<?= $selectedBulan; ?>&tahun=<?= $selectedTahun; ?>" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-1 text-nowrap">
                    <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-group-divider bg-body-secondary">
                        <tr class="text-uppercase small text-body-secondary">
                            <th class="py-3 ps-3 text-center fw-bold" style="width: 40px; text-align: center !important;">No</th>
                            <th class="py-3 px-3 fw-bold">Nama Outlet</th>
                            <th class="py-3 px-3 text-center fw-bold" style="text-align: center !important;">Total Omzet (100%)</th>
                            <th class="py-3 px-3 text-center fw-bold text-danger" style="text-align: center !important;">Potongan Outlet</th>
                            <th class="py-3 px-3 text-center fw-bold text-success" style="text-align: center !important;">Hak Investor</th>
                            <th class="py-3 px-3 text-center fw-bold text-warning" style="text-align: center !important;">Hak Outlet</th>
                            <th class="py-3 px-3 text-center fw-bold text-brown" style="text-align: center !important;">Modal Belanja</th>
                            <th class="py-3 px-3 text-center fw-bold text-body-emphasis" style="text-align: center !important;">Bersih Outlet Total</th>
                            <th class="py-3 px-3 text-center fw-bold pe-3" style="width: 140px; text-align: center !important;">Aksi Detail</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        <?php if (!empty($rows)) : ?>
                            <?php $no = 1; foreach ($rows as $r) : ?>
                                <tr>
                                    <td class="py-3 ps-3 text-center text-body-secondary fw-bold"><?= $no++; ?></td>
                                    <td class="py-3 px-3">
                                        <div class="fw-bold text-body-emphasis fs-6">
                                            <i class="fa-solid fa-store text-danger me-1"></i>
                                            <?= htmlspecialchars($r['nama_outlet']); ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center fw-bold text-body-emphasis" style="text-align: center !important;">Rp <?= number_format($r['total_omzet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-center fw-bold text-danger" style="text-align: center !important;">
                                        <?php if ($r['is_last_day_done']) : ?>
                                            <span>Rp <?= number_format($r['potongan_10'], 0, ',', '.'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">Rp 0 (Belum Dipotong)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center fw-extrabold text-success fs-6" style="text-align: center !important;">
                                        <?php if ($r['is_last_day_done']) : ?>
                                            <span>Rp <?= number_format($r['hak_investor'], 0, ',', '.'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">Rp 0 (Belum Aktif)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center fw-extrabold text-warning fs-6" style="text-align: center !important;">
                                        <?php if ($r['is_last_day_done']) : ?>
                                            <span>Rp <?= number_format($r['hak_outlet'], 0, ',', '.'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">Rp 0 (Belum Aktif)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center fw-bold text-brown" style="text-align: center !important;">
                                        <?php if ($r['is_last_day_done']) : ?>
                                            <span>Rp <?= number_format($r['total_omzet'] - $r['potongan_10'], 0, ',', '.'); ?></span>
                                        <?php else : ?>
                                            <span>Rp <?= number_format($r['total_omzet'], 0, ',', '.'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center fw-bold text-body-emphasis" style="text-align: center !important;">Rp <?= number_format($r['total_bersih_outlet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-center pe-3" style="text-align: center !important;">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold btn-detail-harian-outlet" data-id="<?= $r['id_outlet']; ?>" data-nama="<?= htmlspecialchars($r['nama_outlet']); ?>">
                                            <i class="fa-solid fa-list-check me-1"></i> Rincian Harian
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center text-body-secondary py-5">
                                    <div class="py-3">
                                        <i class="fa-light fa-vault text-body-secondary mb-3" style="font-size: 50px; opacity: 0.5;"></i>
                                        <h5 class="fw-bold text-body-secondary mb-1">Belum Ada Data Outlet / Omzet</h5>
                                        <p class="text-body-secondary small mb-0">Belum ada omzet yang terdaftar pada periode pelaporan ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($rows)) : ?>
                        <tfoot class="table-group-divider bg-body-secondary fw-bold">
                            <tr>
                                <td colspan="2" class="py-3 ps-3 text-end text-body-emphasis text-uppercase">TOTAL KESELURUHAN:</td>
                                <td class="py-3 px-3 text-center text-body-emphasis fs-6" style="text-align: center !important;">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></td>
                                <td class="py-3 px-3 text-center text-danger fs-6" style="text-align: center !important;">
                                    <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totPotongan10, 0, ',', '.') : '-'; ?>
                                </td>
                                <!-- Highlighted ONLY Total Keseluruhan Hak Investor -->
                                <td class="py-3 px-3 text-center text-success fs-5 bg-success-subtle bg-opacity-25" style="border: 2px solid #198754; border-radius: 8px; text-align: center !important;">
                                    <span class="badge bg-success text-white px-3 py-2 fs-5 rounded-pill shadow-xs">
                                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakInvestor, 0, ',', '.') : 'Rp 0'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center text-warning fs-5" style="text-align: center !important;">
                                    <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakOutlet, 0, ',', '.') : '-'; ?>
                                </td>
                                <td class="py-3 px-3 text-center text-brown fs-6 fw-bold" style="text-align: center !important;">
                                    <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totOmzet - $totPotongan10, 0, ',', '.') : 'Rp ' . number_format($totOmzet, 0, ',', '.'); ?>
                                </td>
                                <td class="py-3 px-3 text-center text-body-emphasis fs-6" style="text-align: center !important;">Rp <?= number_format($totOmzet - $totHakInvestor, 0, ',', '.'); ?></td>
                                <td class="py-3 px-3 text-center text-body-secondary pe-3" style="text-align: center !important;">-</td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Jarak Aman Tambahan Sebelum Footer -->
    <div class="pb-4 pb-md-5"></div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: FILTER DATA REKAP BAGI HASIL (Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalFilterBagiHasil" tabindex="-1" aria-labelledby="modalFilterBagiHasilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalFilterBagiHasilLabel">
                    <i class="fa-solid fa-filter me-2 text-danger"></i>Filter Data Rekap Bagi Hasil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="<?= SystemInfo::app('CLIENT_URL'); ?>/bagi-hasil">
                <div class="modal-body p-4">
                    <?php if ($role === 'investor' && !empty($investorOutlets)) : ?>
                        <!-- 1. Pencarian Toko / Outlet Interaktif -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-body-secondary">
                                <i class="fa-solid fa-store me-1 text-danger"></i>Cari & Pilih Toko / Outlet
                            </label>
                            <input type="hidden" name="outlet_id" id="filterModalOutletId" value="<?= $selectedOutletId; ?>">
                            
                            <div class="position-relative">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-secondary border-body-subtle text-body-secondary">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" id="filterModalOutletSearch" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold shadow-none" placeholder="Ketik nama toko untuk mencari..." value="<?= htmlspecialchars($selectedOutletNama ?: 'Semua Toko'); ?>" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetSelectedOutlet" title="Pilih Semua Outlet">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <!-- Badge Status Outlet Terpilih -->
                                <div id="selectedOutletBadgeContainer" class="mt-2">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold" id="selectedOutletBadgeText">
                                        <i class="fa-solid fa-store me-1"></i> Terpilih: <?= htmlspecialchars($selectedOutletNama ?: 'Semua Toko'); ?>
                                    </span>
                                </div>

                                <!-- Box Hasil Pencarian Toko (Live Search Dropdown) -->
                                <div id="outletSearchResultsBox" class="list-group position-absolute w-100 shadow-lg border border-body-subtle rounded-3 mt-1 d-none" style="z-index: 1056; max-height: 220px; overflow-y: auto;">
                                    <!-- Rendered dynamically via JavaScript -->
                                </div>
                            </div>
                            <div class="form-text text-body-secondary small mt-1">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i>Ketik nama toko untuk mencari, atau pilih <strong>Semua Toko</strong> untuk melihat rekapitulasi seluruh toko Anda.
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 2. Rentang Tanggal (Bebas: 1 Hari, 3 Hari, 1 Minggu, dll) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-body-secondary mb-0">
                                <i class="fa-regular fa-calendar-range me-1 text-danger"></i>Pilih Rentang Tanggal (Bebas)
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 fw-bold px-2 py-0" id="btnResetTanggalFilter" style="font-size: 11px;">
                                <i class="fa-solid fa-rotate-left me-1"></i>Reset Tanggal
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="filterModalTglMulai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Mulai</label>
                                <div class="input-group input-group-sm cursor-pointer date-picker-wrapper">
                                    <span class="input-group-text bg-body-tertiary border-body-subtle text-danger">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </span>
                                    <input type="date" name="tgl_mulai" id="filterModalTglMulai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglMulai); ?>" onclick="if(this.showPicker){this.showPicker();}">
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="filterModalTglSelesai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Selesai</label>
                                <div class="input-group input-group-sm cursor-pointer date-picker-wrapper">
                                    <span class="input-group-text bg-body-tertiary border-body-subtle text-danger">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </span>
                                    <input type="date" name="tgl_selesai" id="filterModalTglSelesai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglSelesai); ?>" onclick="if(this.showPicker){this.showPicker();}">
                                </div>
                            </div>
                        </div>
                        <div class="form-text text-body-secondary small mt-2">
                            <i class="fa-solid fa-circle-info me-1 text-primary"></i>*Klik <strong>Reset Tanggal</strong> untuk menghapus filter tanggal dan menampilkan <strong>seluruh akumulasi data</strong> tanpa batasan periode.
                        </div>
                    </div>


                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL RINCIAN OMZET HARIAN TOKO (Tgl 1 s.d. Tgl 31) -->
<!-- ========================================================================= -->
<style>
#tableModalDetailHarian thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: var(--bs-tertiary-bg, #f8fafc) !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
#tableModalDetailHarian tfoot td {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background-color: var(--bs-tertiary-bg, #f8fafc) !important;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.08);
}
#tableModalDetailHarian tfoot td#tfootTotHakInv {
    background-color: #d1e7dd !important;
}
[data-bs-theme="dark"] #tableModalDetailHarian tfoot td#tfootTotHakInv,
.dark-theme #tableModalDetailHarian tfoot td#tfootTotHakInv {
    background-color: #0f5132 !important;
}
</style>

<div class="modal fade" id="modalDetailOmzetHarian" tabindex="-1" aria-labelledby="modalDetailOmzetHarianLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px; width: 96%;">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <h6 class="modal-title fw-bold text-body-emphasis" id="modalDetailOmzetHarianLabel" style="font-size: 14px;">
                    <i class="fa-solid fa-calendar-days text-danger me-2" style="margin-right: 8px !important;"></i>Rincian Omzet Harian: <span id="lblModalNamaOutlet" class="text-danger fw-bold"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 10px;"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Table of Daily Omzet -->
                <div class="table-responsive rounded-3 border" style="max-height: 480px;">
                    <table class="table table-sm table-hover align-middle mb-0 w-100 text-nowrap" id="tableModalDetailHarian" style="font-size: 11.5px;">
                        <thead class="table-group-divider bg-body-secondary text-uppercase fw-bold" style="font-size: 10.5px; letter-spacing: 0.3px;">
                            <tr>
                                <th class="py-2.5 text-center" style="width: 40px;">NO</th>
                                <th class="py-2.5 text-center">TANGGAL LAPORAN</th>
                                <th class="py-2.5 text-center">OMZET HARIAN</th>
                                <th class="py-2.5 text-center text-danger" id="lblModalHeaderPotongan">POTONGAN</th>
                                <th class="py-2.5 text-center text-success" id="lblModalHeaderHakInv">HAK INVESTOR</th>
                                <th class="py-2.5 text-center text-warning" id="lblModalHeaderHakOut">HAK OUTLET</th>
                                <th class="py-2.5 text-center text-brown">MODAL BELANJA</th>
                                <th class="py-2.5 text-center text-body-emphasis">TOTAL DITERIMA</th>
                            </tr>
                        </thead>
                        <tbody class="border-0">
                            <!-- Loaded dynamically via JS -->
                        </tbody>
                        <tfoot class="table-group-divider bg-body-secondary fw-bold d-none" id="tfootModalDetailHarian" style="font-size: 11.5px;">
                            <tr>
                                <td colspan="2" class="py-2.5 text-center text-body-emphasis text-uppercase fw-bold">TOTAL KESELURUHAN:</td>
                                <td class="py-2.5 text-center text-body-emphasis fw-extrabold" id="tfootTotOmzet">Rp 0</td>
                                <td class="py-2.5 text-center text-danger fw-extrabold" id="tfootTotPotongan">Rp 0</td>
                                <td class="py-2.5 text-center text-success fw-extrabold" id="tfootTotHakInv">Rp 0</td>
                                <td class="py-2.5 text-center text-warning fw-extrabold" id="tfootTotHakOut">Rp 0</td>
                                <td class="py-2.5 text-center text-brown fw-extrabold" id="tfootTotKulakan">Rp 0</td>
                                <td class="py-2.5 text-center text-body-emphasis fw-extrabold" id="tfootTotDiterima">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-3 px-3 justify-content-start">
                <button type="button" class="btn btn-light rounded-pill px-4 py-1.5 btn-sm fw-bold" data-bs-dismiss="modal" style="font-size: 12px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Instant Datepicker Pop-up Handler when clicking icon, wrapper, or label
    $(document).on('click', '.date-picker-wrapper', function() {
        const input = $(this).find('input[type="date"]')[0];
        if (input) {
            if (typeof input.showPicker === 'function') {
                try {
                    input.showPicker();
                } catch(err) {
                    input.focus();
                }
            } else {
                input.focus();
            }
        }
    });

    // Reset Tanggal Filter Event
    $('#btnResetTanggalFilter').on('click', function() {
        $('#filterModalTglMulai').val('');
        $('#filterModalTglSelesai').val('');
    });

    // Trigger Modal Rincian Omzet Harian
    $(document).on('click', '.btn-detail-harian-outlet', function() {
        const idOutlet = $(this).data('id');
        const namaOutlet = $(this).data('nama');
        const tglMulai = '<?= htmlspecialchars($selectedTglMulai); ?>';
        const tglSelesai = '<?= htmlspecialchars($selectedTglSelesai); ?>';
        const bulan = '<?= $selectedBulan; ?>';
        const tahun = '<?= $selectedTahun; ?>';

        $('#lblModalNamaOutlet').text(namaOutlet);
        $('#tfootModalDetailHarian').addClass('d-none');
        $('#tableModalDetailHarian tbody').html(`
            <tr>
                <td colspan="8" class="text-center py-4 text-body-secondary">
                    <i class="fa-solid fa-spinner fa-spin me-2 text-danger fs-5"></i>Memuat rincian omzet harian...
                </td>
            </tr>
        `);
        $('#modalDetailOmzetHarian').modal('show');

        $.ajax({
            url: '<?= SystemInfo::app('CLIENT_URL'); ?>/doc/bagi-hasil/action.php',
            type: 'GET',
            data: {
                action: 'get_detail_harian',
                id_outlet: idOutlet,
                tgl_mulai: tglMulai,
                tgl_selesai: tglSelesai,
                bulan: bulan,
                tahun: tahun
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const tbody = $('#tableModalDetailHarian tbody');
                    tbody.empty();

                    if (res.items && res.items.length > 0) {
                        const fmt = new Intl.NumberFormat('id-ID');
                        
                        // Update headers dynamically
                        $('#lblModalHeaderPotongan').html(res.rate_potongan === 'Variatif' ? 'Potongan (Variatif)' : `Potongan (${res.rate_potongan})`);
                        $('#lblModalHeaderHakInv').html(res.persen_inv === 'Variatif' ? 'Hak Investor (Variatif)' : `Hak Investor (${res.persen_inv})`);
                        $('#lblModalHeaderHakOut').html(res.persen_out === 'Variatif' ? 'Hak Outlet (Variatif)' : `Hak Outlet (${res.persen_out})`);

                        res.items.forEach((item, idx) => {
                            const modalBelanja = item.omzet - item.potongan_10;
                            const totalDiterima = modalBelanja + item.hak_outlet;
                            tbody.append(`
                                <tr>
                                    <td class="text-center py-1.5 px-2 fw-bold text-body-secondary">${idx + 1}</td>
                                    <td class="text-center py-1.5 px-2 fw-bold text-body-emphasis">
                                        <i class="fa-regular fa-calendar-day me-1 text-danger"></i>${item.tgl_formatted}
                                    </td>
                                    <td class="text-center py-1.5 px-2 fw-bold text-body-emphasis">Rp ${fmt.format(item.omzet)}</td>
                                    <td class="text-center py-1.5 px-2 fw-semibold text-danger">
                                        Rp ${fmt.format(item.potongan_10)}
                                        <span class="badge bg-danger-subtle text-danger fw-bold ms-1" style="font-size: 10px; padding: 2px 5px; border-radius: 4px; border: 1px solid rgba(220, 53, 69, 0.15);">${item.rate_potongan}%</span>
                                    </td>
                                    <td class="text-center py-1.5 px-2 fw-bold text-success">
                                        Rp ${fmt.format(item.hak_investor)}
                                        <span class="badge bg-success-subtle text-success fw-bold ms-1" style="font-size: 10px; padding: 2px 5px; border-radius: 4px; border: 1px solid rgba(25, 135, 84, 0.15);">${item.persen_investor}%</span>
                                    </td>
                                    <td class="text-center py-1.5 px-2 fw-semibold text-warning">
                                        Rp ${fmt.format(item.hak_outlet)}
                                        <span class="badge bg-warning-subtle text-warning fw-bold ms-1" style="font-size: 10px; padding: 2px 5px; border-radius: 4px; border: 1px solid rgba(255, 193, 7, 0.2);">${item.persen_outlet}%</span>
                                    </td>
                                    <td class="text-center py-1.5 px-2 fw-bold text-brown">Rp ${fmt.format(modalBelanja)}</td>
                                    <td class="text-center py-1.5 px-2 fw-bold text-body-emphasis">Rp ${fmt.format(totalDiterima)}</td>
                                </tr>
                            `);
                        });

                        // Set Foot Values
                        const totBelanja = res.summary.total_omzet - res.summary.total_potongan;
                        const totDiterima = totBelanja + res.summary.total_hak_outlet;
                        $('#tfootTotOmzet').text('Rp ' + fmt.format(res.summary.total_omzet));
                        $('#tfootTotPotongan').text('Rp ' + fmt.format(res.summary.total_potongan));
                        $('#tfootTotHakInv').text('Rp ' + fmt.format(res.summary.total_hak_investor));
                        $('#tfootTotHakOut').text('Rp ' + fmt.format(res.summary.total_hak_outlet));
                        $('#tfootTotKulakan').text('Rp ' + fmt.format(totBelanja));
                        $('#tfootTotDiterima').text('Rp ' + fmt.format(totDiterima));
                        $('#tfootModalDetailHarian').removeClass('d-none');
                    } else {
                        $('#tfootModalDetailHarian').addClass('d-none');
                        tbody.html(`
                            <tr>
                                <td colspan="8" class="text-center py-4 text-body-secondary">
                                    Belum ada catatan omzet harian pada periode ini.
                                </td>
                            </tr>
                        `);
                    }
                } else {
                    $('#tfootModalDetailHarian').addClass('d-none');
                    $('#tableModalDetailHarian tbody').html(`
                        <tr>
                            <td colspan="8" class="text-center py-4 text-danger fw-semibold">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>${res.message || 'Gagal memuat rincian.'}
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#tfootModalDetailHarian').addClass('d-none');
                $('#tableModalDetailHarian tbody').html(`
                    <tr>
                        <td colspan="8" class="text-center py-4 text-danger fw-semibold">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Terjadi kendala jaringan saat memuat data.
                        </td>
                    </tr>
                `);
            }
        });
    });

    // Live Search Store / Outlet Filtering Logic
    const investorOutlets = <?= json_encode($investorOutlets); ?>;
    const searchInput = $('#filterModalOutletSearch');
    const resultsBox = $('#outletSearchResultsBox');
    const hiddenInput = $('#filterModalOutletId');
    const badgeContainer = $('#selectedOutletBadgeContainer');
    const badgeText = $('#selectedOutletBadgeText');
    let searchTimeout = null;

    function renderResults(query) {
        if (!investorOutlets || investorOutlets.length === 0) return;

        const cleanQuery = (query || '').trim().toLowerCase();

        resultsBox.removeClass('d-none').empty();

        const isAllSelected = parseInt(hiddenInput.val()) === 0;
        
        // Always include "Semua Toko" option at top
        resultsBox.append(`
            <button type="button" class="list-group-item list-group-item-action ${isAllSelected ? 'bg-danger-subtle text-danger fw-bold' : 'bg-body text-body-emphasis'} small py-2.5 px-3 border-bottom btn-select-outlet" data-id="0" data-name="Semua Toko">
                <i class="fa-solid fa-store me-2 ${isAllSelected ? 'text-danger' : 'text-primary'}"></i><strong>Semua Toko</strong> <span class="small text-body-secondary ms-1">(Rekap Akumulasi Seluruh Toko)</span>
            </button>
        `);

        const filtered = investorOutlets.filter(function(item) {
            if (!cleanQuery || cleanQuery === 'semua toko') return true;
            return (item.nama_outlet && item.nama_outlet.toLowerCase().includes(cleanQuery));
        });

        if (filtered.length > 0) {
            filtered.forEach(function(item) {
                const isSelected = parseInt(item.id_outlet) === parseInt(hiddenInput.val());
                const activeClass = isSelected ? 'bg-danger text-white fw-bold' : 'bg-body text-body-emphasis';
                const iconClass = isSelected ? 'text-white' : 'text-danger';

                resultsBox.append(`
                    <button type="button" class="list-group-item list-group-item-action ${activeClass} small py-2.5 px-3 btn-select-outlet" data-id="${item.id_outlet}" data-name="${item.nama_outlet}">
                        <i class="fa-solid fa-store me-2 ${iconClass}"></i>${item.nama_outlet}
                    </button>
                `);
            });
        } else if (cleanQuery.length > 0 && cleanQuery !== 'semua toko') {
            resultsBox.append(`
                <div class="list-group-item bg-body text-danger small py-3 text-center fw-semibold border-0">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> Data toko "${query}" tidak ditemukan
                </div>
            `);
        }
    }

    searchInput.on('focus', function() {
        if ($(this).val() === 'Semua Toko') {
            $(this).select();
        }
        renderResults($(this).val() === 'Semua Toko' ? '' : $(this).val());
    });

    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const q = $(this).val();
        searchTimeout = setTimeout(function() {
            renderResults(q);
        }, 150);
    });

    $(document).on('click', '.btn-select-outlet', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = parseInt($(this).data('id'));
        const name = $(this).data('name') || 'Semua Toko';

        if (isNaN(id) || id === 0) {
            hiddenInput.val(0);
            searchInput.val('Semua Toko');
            if (badgeText.length) badgeText.html('<i class="fa-solid fa-store me-1"></i> Terpilih: Semua Toko');
        } else {
            hiddenInput.val(id);
            searchInput.val(name);
            if (badgeText.length) badgeText.html('<i class="fa-solid fa-store me-1"></i> Terpilih: ' + name);
        }
        if (badgeContainer.length) badgeContainer.removeClass('d-none');
        resultsBox.addClass('d-none');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#filterModalOutletSearch, #outletSearchResultsBox, #btnResetSelectedOutlet').length) {
            resultsBox.addClass('d-none');
        }
    });

    $('#btnResetSelectedOutlet').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        hiddenInput.val(0);
        searchInput.val('Semua Toko');
        if (badgeText.length) badgeText.html('<i class="fa-solid fa-store me-1"></i> Terpilih: Semua Toko');
        if (badgeContainer.length) badgeContainer.removeClass('d-none');
        resultsBox.addClass('d-none');
    });
});
</script>
