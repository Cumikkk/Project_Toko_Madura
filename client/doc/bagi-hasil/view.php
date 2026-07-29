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
    // Get Investor ID & percentage for logged in investor
    $resInv = $db->query("SELECT id_investor, persen_bagian_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
    if ($resInv && $resInv->num_rows > 0) {
        $rowInv = $resInv->fetch_assoc();
        $investorId = (int)$rowInv['id_investor'];
        $persenInvestor = (float)$rowInv['persen_bagian_investor'];
    }
} else {
    // Logged in user is Outlet
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
$potonganGlobal = 10.00; // Default 10%
if ($resSet && $resSet->num_rows > 0) {
    $potonganGlobal = (float)$resSet->fetch_assoc()['nilai'];
}

// Filter Logic (Outlet, Rentang Tanggal, Bulan, Tahun)
$selectedOutletId   = isset($_GET['outlet_id']) ? (int)$_GET['outlet_id'] : (isset($_GET['id_outlet']) ? (int)$_GET['id_outlet'] : (isset($_GET['outlet']) ? (int)$_GET['outlet'] : 0));
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

if (!isset($_GET['outlet_id']) && !isset($_GET['id_outlet']) && !isset($_GET['outlet']) && !isset($_GET['tgl_mulai']) && !isset($_GET['tgl_selesai']) && !isset($_GET['bulan']) && !isset($_GET['tahun'])) {
    $selectedBulan = (int)date('n');
    $selectedTahun = (int)date('Y');
}

// Fetch list of outlets belonging to logged in investor
$investorOutlets = [];
if ($role === 'investor' && $investorId > 0) {
    $resOuts = $db->query("SELECT id_outlet, nama_outlet, kode_outlet FROM outlet WHERE id_investor = {$investorId} ORDER BY nama_outlet ASC");
    if ($resOuts) {
        while ($oRow = $resOuts->fetch_assoc()) {
            $investorOutlets[] = $oRow;
        }
    }
}

// Determine last day of selected month/year to check if deduction is active
$checkBulan = ($selectedBulan > 0) ? $selectedBulan : (int)date('n');
$checkTahun = ($selectedTahun > 0) ? $selectedTahun : (int)date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $checkBulan, $checkTahun);
$lastDayDateStr = sprintf('%04d-%02d-%02d', $checkTahun, $checkBulan, $daysInMonth);

$availableYears = [];
$rows = [];
$totOmzet = 0;
$totPotongan10 = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;
$hasAnyLastDayDone = false;

// Fetch distinct years available in database
$whereYearSql = ($role === 'investor') ? "o.id_investor = {$investorId}" : "o.id_outlet = {$targetOutletId}";
$resYears = $db->query("SELECT DISTINCT YEAR(l.periode_laporan) as y_periode FROM laporan_omzet l JOIN outlet o ON l.id_outlet = o.id_outlet WHERE {$whereYearSql} ORDER BY y_periode DESC");
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
    $whereConditions[] = "l.periode_laporan BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
    
    if ($selectedTglMulai === $selectedTglSelesai) {
        $periodeParts[] = date('d/m/Y', strtotime($selectedTglMulai));
    } else {
        $periodeParts[] = date('d/m/Y', strtotime($selectedTglMulai)) . ' - ' . date('d/m/Y', strtotime($selectedTglSelesai));
    }
} elseif (!empty($selectedTglMulai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $whereConditions[] = "l.periode_laporan >= '{$safeMulai}'";
    $periodeParts[] = 'Mulai ' . date('d/m/Y', strtotime($selectedTglMulai));
} elseif (!empty($selectedTglSelesai)) {
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConditions[] = "l.periode_laporan <= '{$safeSelesai}'";
    $periodeParts[] = 's/d ' . date('d/m/Y', strtotime($selectedTglSelesai));
} else {
    if ($selectedBulan > 0) {
        $whereConditions[] = "MONTH(l.periode_laporan) = {$selectedBulan}";
        $periodeParts[] = $bulanIndo[$selectedBulan] ?? '';
    }
    if ($selectedTahun > 0) {
        $whereConditions[] = "YEAR(l.periode_laporan) = {$selectedTahun}";
        $periodeParts[] = $selectedTahun;
    }
}

$periodeTitleStr = !empty($periodeParts) ? implode(" ", $periodeParts) : "Semua Periode";
$periodeLabelStr = $periodeTitleStr;
if (!empty($selectedOutletNama)) {
    $periodeLabelStr .= " " . $selectedOutletNama;
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
        o.kode_outlet,
        o.nama_outlet,
        IFNULL(SUM(l.omzet), 0) as total_omzet,
        IFNULL(SUM(l.nominal_potongan), 0) as total_potongan_db
    FROM outlet o
    LEFT JOIN laporan_omzet l ON {$joinOnClause}
    WHERE {$whereConditions[0]}
    GROUP BY o.id_outlet
    ORDER BY o.id_outlet DESC
";

$resBagiHasil = $db->query($sqlBagiHasil);

if ($resBagiHasil) {
    while ($row = $resBagiHasil->fetch_assoc()) {
        $omzet = (float)$row['total_omzet'];
        $idOutletRow = (int)$row['id_outlet'];
        
        $isCurrentFilterLastDay = true;
        if (!empty($selectedTgl)) {
            $tglNum = (int)date('j', strtotime($selectedTgl));
            if ($tglNum !== $daysInMonth) {
                $isCurrentFilterLastDay = false;
            }
        }

        $chkLast = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutletRow} AND periode_laporan = '{$lastDayDateStr}' LIMIT 1");
        $isLastDayDoneInDb = ($chkLast && $chkLast->num_rows > 0);

        $isLastDayDone = ($isLastDayDoneInDb && $isCurrentFilterLastDay);

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

        // Total Pendapatan Bersih Outlet (Omzet - Hak Investor)
        $totalBersihOutlet = $isLastDayDone ? (($omzet - $potongan10) + $hakOutlet) : $omzet;

        $row['is_last_day_done'] = $isLastDayDone;
        $row['potongan_10'] = $potongan10;
        $row['hak_investor'] = $hakInvestor;
        $row['hak_outlet'] = $hakOutlet;
        $row['total_bersih_outlet'] = $totalBersihOutlet;

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

                <!-- Controls: Export PDF -->
                <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-lg-auto justify-content-start justify-content-lg-end">
                    <!-- Tombol Cetak PDF Bagi Hasil -->
                    <a href="<?= SystemInfo::app('CLIENT_URL'); ?>/doc/bagi-hasil/export_pdf.php?outlet_id=<?= $selectedOutletId; ?>&tgl_mulai=<?= urlencode($selectedTglMulai); ?>&tgl_selesai=<?= urlencode($selectedTglSelesai); ?>&bulan=<?= $selectedBulan; ?>&tahun=<?= $selectedTahun; ?>" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3 py-2 shadow-sm fw-bold text-nowrap flex-grow-1 flex-lg-grow-0 text-center">
                        <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF Bagi Hasil
                    </a>
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
                            <i class="fa-solid fa-circle-check me-1 text-success"></i>Bagi Hasil Investor (50% dari 10%) Aktif
                        <?php else : ?>
                            <i class="fa-light fa-clock me-1 text-warning"></i>Pendataan berjalan (Menunggu Tgl akhir bulan <?= $daysInMonth; ?>)
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Summary Metric Cards (Mobile Readable - No Truncate) -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- 1. Total Omzet Reported -->
        <div class="col-6 col-xl-3">
            <div class="box-stat-bagi-hasil box-stat-omzet h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-primary text-uppercase card-stat-title-full">Total Omzet Toko (100%)</span>
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

        <!-- 2. Potongan 10% -->
        <div class="col-6 col-xl-3">
            <div class="box-stat-bagi-hasil box-stat-potongan h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-danger text-uppercase card-stat-title-full">Potongan (10%)</span>
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

        <!-- 3. Hak Investor (50%) -->
        <div class="col-6 col-xl-3">
            <div class="box-stat-bagi-hasil box-stat-investor h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-success text-uppercase card-stat-title-full">Hak Investor (50%)</span>
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

        <!-- 4. Hak Outlet (50%) -->
        <div class="col-6 col-xl-3">
            <div class="box-stat-bagi-hasil box-stat-outlet h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-body-emphasis text-uppercase card-stat-title-full">Hak Outlet (50%)</span>
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
    </div>

    <!-- Breakdown Table Per Outlet (Sleek Theme-Adaptive Card Container) -->
    <div class="card border border-body-subtle shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-body-emphasis mb-0 fs-6">
                    <i class="fa-solid fa-list-check me-2 text-danger"></i>Rincian Pembagian Hak Per Outlet (<?= htmlspecialchars($periodeLabelStr); ?>)
                </h5>
                <p class="text-body-secondary small mb-0">Rincian omzet, nominal potongan 10%, serta hak investor & outlet</p>
            </div>
            <!-- Tombol Filter Utama (Menggantikan Rekap Final) -->
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterBagiHasil">
                <i class="fa-solid fa-filter me-1"></i> Filter Data
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-group-divider bg-body-secondary">
                        <tr class="text-uppercase small text-body-secondary">
                            <th class="py-3 ps-3 text-center fw-bold" style="width: 40px;">No</th>
                            <th class="py-3 px-3 fw-bold">Nama Outlet</th>
                            <th class="py-3 px-3 text-end fw-bold">Total Omzet (100%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-danger">Potongan (10%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-success">Hak Investor (50%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-warning">Hak Outlet (50%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-body-emphasis">Bersih Outlet Total</th>
                            <th class="py-3 px-3 text-center fw-bold pe-3" style="width: 140px;">Aksi Detail</th>
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
                                    <td class="py-3 px-3 text-end fw-bold text-body-emphasis">Rp <?= number_format($r['total_omzet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-bold text-danger">
                                        <?php if ($r['is_last_day_done']) : ?>
                                            Rp <?= number_format($r['potongan_10'], 0, ',', '.'); ?>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">Rp 0 (Belum Dipotong)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-end fw-extrabold text-success fs-6">
                                        <?php if ($r['is_last_day_done']) : ?>
                                            Rp <?= number_format($r['hak_investor'], 0, ',', '.'); ?>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">Rp 0 (Belum Aktif)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-end fw-extrabold text-warning fs-6">
                                        <?php if ($r['is_last_day_done']) : ?>
                                            Rp <?= number_format($r['hak_outlet'], 0, ',', '.'); ?>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">Rp 0 (Belum Aktif)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-end fw-bold text-body-emphasis">Rp <?= number_format($r['total_bersih_outlet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-center pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold btn-detail-harian-outlet" data-id="<?= $r['id_outlet']; ?>" data-nama="<?= htmlspecialchars($r['nama_outlet']); ?>">
                                            <i class="fa-solid fa-list-check me-1"></i> Rincian Harian
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center text-body-secondary py-5">
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
                                <td class="py-3 px-3 text-end text-body-emphasis fs-6">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></td>
                                <td class="py-3 px-3 text-end text-danger fs-6">
                                    <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totPotongan10, 0, ',', '.') : '-'; ?>
                                </td>
                                <!-- Highlighted ONLY Total Keseluruhan Hak Investor -->
                                <td class="py-3 px-3 text-end text-success fs-5 bg-success-subtle bg-opacity-25" style="border: 2px solid #198754; border-radius: 8px;">
                                    <span class="badge bg-success text-white px-3 py-2 fs-5 rounded-pill shadow-xs">
                                        <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakInvestor, 0, ',', '.') : 'Rp 0'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-end text-warning fs-5">
                                    <?= ($hasAnyLastDayDone || $selectedBulan === 0) ? 'Rp ' . number_format($totHakOutlet, 0, ',', '.') : '-'; ?>
                                </td>
                                <td class="py-3 px-3 text-end text-body-emphasis fs-6">Rp <?= number_format($totOmzet - $totHakInvestor, 0, ',', '.'); ?></td>
                                <td class="py-3 px-3 text-center text-body-secondary pe-3">-</td>
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
                        <!-- 1. Pencarian Outlet Interaktif -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-body-secondary">
                                <i class="fa-solid fa-store me-1 text-danger"></i>Cari & Pilih Toko / Outlet
                            </label>
                            <input type="hidden" name="outlet_id" id="filterModalOutletId" value="<?= $selectedOutletId; ?>">
                            
                            <div class="position-relative">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-secondary border-body-subtle text-body-secondary">
                                        <i class="fa-light fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" id="filterModalOutletSearch" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold shadow-none" placeholder="Ketik nama toko..." value="<?= htmlspecialchars($selectedOutletNama); ?>" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetSelectedOutlet" title="Pilih Semua Outlet">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <!-- Badge Status Outlet Terpilih -->
                                <div id="selectedOutletBadgeContainer" class="mt-2 <?= ($selectedOutletId > 0) ? '' : 'd-none'; ?>">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold" id="selectedOutletBadgeText">
                                        <i class="fa-solid fa-store me-1"></i> Terpilih: <?= htmlspecialchars($selectedOutletNama ?: 'Semua Outlet'); ?>
                                    </span>
                                </div>

                                <!-- Box Hasil Pencarian Toko (Live Search Dropdown) -->
                                <div id="outletSearchResultsBox" class="list-group position-absolute w-100 shadow-lg border border-body-subtle rounded-3 mt-1 d-none" style="z-index: 1056; max-height: 220px; overflow-y: auto;">
                                    <!-- Rendered dynamically via JavaScript -->
                                </div>
                            </div>
                            <div class="form-text text-body-secondary small mt-1">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i>Pilih <strong>Semua Toko</strong> untuk melihat rekapitulasi akumulasi seluruh toko Anda, atau ketik nama toko untuk melihat per toko.
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 2. Rentang Tanggal (Bebas: 1 Hari, 3 Hari, 1 Minggu, dll) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-body-secondary"><i class="fa-regular fa-calendar-range me-1 text-danger"></i>Pilih Rentang Tanggal (Bebas)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-body-secondary d-block mb-1">Tanggal Mulai</small>
                                <input type="date" name="tgl_mulai" class="form-control form-control-sm bg-body border-body-subtle text-body-emphasis fw-semibold" value="<?= htmlspecialchars($selectedTglMulai); ?>">
                            </div>
                            <div class="col-6">
                                <small class="text-body-secondary d-block mb-1">Tanggal Selesai</small>
                                <input type="date" name="tgl_selesai" class="form-control form-control-sm bg-body border-body-subtle text-body-emphasis fw-semibold" value="<?= htmlspecialchars($selectedTglSelesai); ?>">
                            </div>
                        </div>
                        <div class="form-text text-body-secondary micro-text mt-1">
                            *Bisa pilih 1 hari (samakan tgl mulai & selesai), 3 hari, 1 minggu, atau bebas.
                        </div>
                    </div>

                    <!-- 4. Filter Bulan & Tahun -->
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-body-secondary"><i class="fa-regular fa-calendar-days me-1 text-danger"></i>Filter Bulan</label>
                            <select name="bulan" class="form-select bg-body border-body-subtle text-body-emphasis fw-semibold">
                                <option value="0" <?= ($selectedBulan === 0) ? 'selected' : ''; ?>>Semua Bulan</option>
                                <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                    <option value="<?= $mNum; ?>" <?= ($selectedBulan === $mNum) ? 'selected' : ''; ?>><?= $mName; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-body-secondary"><i class="fa-regular fa-calendar-lines me-1 text-danger"></i>Filter Tahun</label>
                            <select name="tahun" class="form-select bg-body border-body-subtle text-body-emphasis fw-semibold">
                                <option value="0" <?= ($selectedTahun === 0) ? 'selected' : ''; ?>>Semua Tahun</option>
                                <?php foreach ($availableYears as $yVal) : ?>
                                    <option value="<?= $yVal; ?>" <?= ($selectedTahun === $yVal) ? 'selected' : ''; ?>><?= $yVal; ?></option>
                                <?php endforeach; ?>
                            </select>
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
<div class="modal fade" id="modalDetailOmzetHarian" tabindex="-1" aria-labelledby="modalDetailOmzetHarianLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalDetailOmzetHarianLabel">
                    <i class="fa-solid fa-calendar-days me-2 text-danger"></i>Rincian Omzet Harian: <span id="lblModalNamaOutlet" class="text-danger"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Table of Daily Omzet -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="tableModalDetailHarian">
                        <thead class="table-group-divider bg-body-secondary small text-body-secondary text-uppercase">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th>Tanggal Laporan</th>
                                <th class="text-end">Omzet Harian</th>
                                <th class="text-end text-danger">Potongan 10%</th>
                                <th class="text-end text-success">Hak Investor (50%)</th>
                                <th class="text-end text-warning pe-3">Hak Outlet (50%)</th>
                            </tr>
                        </thead>
                        <tbody class="border-0 small">
                            <!-- Loaded dynamically via JS -->
                        </tbody>
                        <tfoot class="table-group-divider bg-body-secondary small fw-bold d-none" id="tfootModalDetailHarian">
                            <tr>
                                <td colspan="2" class="py-3 ps-3 text-end text-body-emphasis text-uppercase fw-bold">TOTAL KESELURUHAN:</td>
                                <td class="py-3 text-end text-body-emphasis fs-6 fw-extrabold" id="tfootTotOmzet">Rp 0</td>
                                <td class="py-3 text-end text-danger fs-6 fw-extrabold" id="tfootTotPotongan">Rp 0</td>
                                <td class="py-3 text-end text-success fs-6 fw-extrabold bg-success-subtle bg-opacity-25" id="tfootTotHakInv">Rp 0</td>
                                <td class="py-3 text-end text-warning fs-6 fw-extrabold pe-3" id="tfootTotHakOut">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const investorOutlets = <?= json_encode($investorOutlets); ?>;
    const searchInput = $('#filterModalOutletSearch');
    const resultsBox = $('#outletSearchResultsBox');
    const hiddenInput = $('#filterModalOutletId');
    const badgeContainer = $('#selectedOutletBadgeContainer');
    const badgeText = $('#selectedOutletBadgeText');
    let searchTimeout = null;

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
                <td colspan="6" class="text-center py-4 text-body-secondary">
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
                        res.items.forEach((item, idx) => {
                            tbody.append(`
                                <tr>
                                    <td class="ps-3 fw-bold text-body-secondary">${idx + 1}</td>
                                    <td class="fw-bold text-body-emphasis">
                                        <i class="fa-regular fa-calendar-day me-1 text-danger"></i>${item.tgl_formatted}
                                    </td>
                                    <td class="text-end fw-bold text-body-emphasis">Rp ${fmt.format(item.omzet)}</td>
                                    <td class="text-end fw-semibold text-danger">Rp ${fmt.format(item.potongan_10)}</td>
                                    <td class="text-end fw-bold text-success">Rp ${fmt.format(item.hak_investor)}</td>
                                    <td class="text-end fw-semibold text-warning pe-3">Rp ${fmt.format(item.hak_outlet)}</td>
                                </tr>
                            `);
                        });

                        // Set Foot Values
                        $('#tfootTotOmzet').text('Rp ' + fmt.format(res.summary.total_omzet));
                        $('#tfootTotPotongan').text('Rp ' + fmt.format(res.summary.total_potongan));
                        $('#tfootTotHakInv').text('Rp ' + fmt.format(res.summary.total_hak_investor));
                        $('#tfootTotHakOut').text('Rp ' + fmt.format(res.summary.total_hak_outlet));
                        $('#tfootModalDetailHarian').removeClass('d-none');
                    } else {
                        $('#tfootModalDetailHarian').addClass('d-none');
                        tbody.html(`
                            <tr>
                                <td colspan="6" class="text-center py-4 text-body-secondary">
                                    Belum ada catatan omzet harian pada periode ini.
                                </td>
                            </tr>
                        `);
                    }
                } else {
                    $('#tfootModalDetailHarian').addClass('d-none');
                    $('#tableModalDetailHarian tbody').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4 text-danger fw-semibold">
                                ${res.message}
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#tfootModalDetailHarian').addClass('d-none');
                $('#tableModalDetailHarian tbody').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger fw-semibold">
                            Terjadi kesalahan saat memuat data rincian harian.
                        </td>
                    </tr>
                `);
            }
        });
    });

    function renderResults(query) {
        if (!investorOutlets || investorOutlets.length === 0) return;

        const cleanQuery = (query || '').trim().toLowerCase();

        if (cleanQuery.length === 0) {
            resultsBox.removeClass('d-none').empty();
            const isAllSelected = parseInt(hiddenInput.val()) === 0;
            resultsBox.append(`
                <button type="button" class="list-group-item list-group-item-action ${isAllSelected ? 'bg-danger-subtle text-danger fw-bold' : 'bg-body text-body-emphasis'} small py-2.5 px-3 btn-select-outlet" data-id="0" data-name="">
                    <i class="fa-solid fa-store me-2 ${isAllSelected ? 'text-danger' : 'text-primary'}"></i><strong>Semua Toko</strong> <span class="small text-body-secondary ms-1">(Rekap Akumulasi Seluruh Toko)</span>
                </button>
            `);
            return;
        }

        resultsBox.removeClass('d-none').html(`
            <div class="list-group-item bg-body text-body-secondary small py-3 text-center fw-semibold border-0">
                <i class="fa-solid fa-spinner fa-spin me-2 text-danger"></i>Mencari toko...
            </div>
        `);

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            resultsBox.empty();

            const isAllSelected = parseInt(hiddenInput.val()) === 0;
            resultsBox.append(`
                <button type="button" class="list-group-item list-group-item-action ${isAllSelected ? 'bg-danger-subtle text-danger fw-bold' : 'bg-body text-body-emphasis'} small py-2.5 px-3 border-bottom btn-select-outlet" data-id="0" data-name="">
                    <i class="fa-solid fa-store me-2 ${isAllSelected ? 'text-danger' : 'text-primary'}"></i><strong>Semua Toko</strong> <span class="small text-body-secondary ms-1">(Rekap Akumulasi Seluruh Toko)</span>
                </button>
            `);

            const filtered = investorOutlets.filter(function(item) {
                return (item.nama_outlet && item.nama_outlet.toLowerCase().includes(cleanQuery)) || 
                       (item.kode_outlet && item.kode_outlet.toLowerCase().includes(cleanQuery));
            });

            if (filtered.length > 0) {
                filtered.forEach(function(item) {
                    const isSelected = parseInt(item.id_outlet) === parseInt(hiddenInput.val());
                    const activeClass = isSelected ? 'bg-danger text-white fw-bold' : 'bg-body text-body-emphasis';
                    const iconClass = isSelected ? 'text-white' : 'text-danger';

                    resultsBox.append(`
                        <button type="button" class="list-group-item list-group-item-action ${activeClass} small py-2 px-3 btn-select-outlet" data-id="${item.id_outlet}" data-name="${item.nama_outlet}">
                            <i class="fa-solid fa-store me-2 ${iconClass}"></i>${item.nama_outlet}
                        </button>
                    `);
                });
            } else {
                resultsBox.append(`
                    <div class="list-group-item bg-body text-danger small py-3 text-center fw-semibold border-0">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> Data toko tidak ditemukan
                    </div>
                `);
            }
        }, 200);
    }

    searchInput.on('focus', function() {
        if ($(this).val() === 'Semua Toko') {
            $(this).select();
        }
        renderResults($(this).val() === 'Semua Toko' ? '' : $(this).val());
    });

    searchInput.on('input', function() {
        renderResults($(this).val());
    });

    $(document).on('click', '.btn-select-outlet', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = parseInt($(this).data('id'));
        const name = $(this).data('name') || '';

        if (isNaN(id) || id === 0) {
            hiddenInput.val(0);
            searchInput.val('Semua Toko');
            badgeText.html('<i class="fa-solid fa-store me-1"></i> Terpilih: Semua Toko');
            badgeContainer.removeClass('d-none');
        } else {
            hiddenInput.val(id);
            searchInput.val(name);
            badgeText.html('<i class="fa-solid fa-store me-1"></i> Terpilih: ' + name);
            badgeContainer.removeClass('d-none');
        }
        resultsBox.addClass('d-none');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#filterModalOutletSearch, #outletSearchResultsBox').length) {
            resultsBox.addClass('d-none');
        }
    });

    $('#btnResetSelectedOutlet').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        hiddenInput.val(0);
        searchInput.val('Semua Toko');
        badgeText.html('<i class="fa-solid fa-store me-1"></i> Terpilih: Semua Toko');
        badgeContainer.removeClass('d-none');
        resultsBox.addClass('d-none');
    });
});
</script>
