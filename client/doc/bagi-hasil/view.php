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

// Separate Month & Year Filter
$selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$availableYears = [];
$rows = [];
$totOmzet = 0;
$totPotongan10 = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;

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

// Build WHERE clause based on Month & Year filter
$whereConditions = ($role === 'investor') ? ["o.id_investor = {$investorId}"] : ["o.id_outlet = {$targetOutletId}"];
if ($selectedBulan > 0) {
    $whereConditions[] = "MONTH(l.periode_laporan) = {$selectedBulan}";
}
if ($selectedTahun > 0) {
    $whereConditions[] = "YEAR(l.periode_laporan) = {$selectedTahun}";
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
    LEFT JOIN laporan_omzet l ON o.id_outlet = l.id_outlet AND " . implode(" AND ", array_filter($whereConditions, fn($c) => strpos($c, 'o.') === false)) . "
    WHERE {$whereConditions[0]}
    GROUP BY o.id_outlet
    ORDER BY o.id_outlet DESC
";

$resBagiHasil = $db->query($sqlBagiHasil);

if ($resBagiHasil) {
    while ($row = $resBagiHasil->fetch_assoc()) {
        $omzet = (float)$row['total_omzet'];
        
        // Potongan 10% dari total omzet outlet sebulan
        $potongan10 = round($omzet * ($potonganGlobal / 100.0), 2);
        
        // Bagi Hasil: 50% Investor : 50% Outlet dari potongan 10%
        $hakInvestor = round($potongan10 * ($persenInvestor / 100.0), 2);
        $hakOutlet = round($potongan10 * ($persenOutletBagiHasil / 100.0), 2);
        
        // Total Pendapatan Bersih Outlet (Omzet - Hak Investor)
        $totalBersihOutlet = $omzet - $hakInvestor;

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

$periodeLabelStr = ($selectedBulan > 0 ? ($bulanIndo[$selectedBulan] ?? '') . ' ' : '') . ($selectedTahun > 0 ? $selectedTahun : '');
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
    padding: 4px 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.filter-pill-container select {
    border: none !important;
    background: transparent !important;
    font-weight: 700 !important;
    color: var(--bs-body-color) !important;
    font-size: 13px;
    padding-left: 4px;
    padding-right: 24px;
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

<div class="main-content-inner py-3 py-md-4">
    <!-- Header Title & Filters (Fully Mobile-Precise & Indented) -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-3 mb-md-4 gap-2 gap-md-3">
        <!-- Title Group with Icon & Indented Subtitle -->
        <div class="d-flex align-items-start gap-2 gap-md-3 pe-md-2">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-danger-subtle text-danger p-2 flex-shrink-0 mt-1" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-vault fs-5"></i>
            </div>
            <div>
                <h3 class="fw-extrabold text-body-emphasis mb-1 fs-5 fs-md-4" style="letter-spacing: -0.3px; line-height: 1.25;">
                    Laporan Bagi Hasil <?= ($role === 'investor') ? 'Investor' : 'Toko'; ?>
                </h3>
                <p class="text-body-secondary small mb-0" style="line-height: 1.35; font-size: 12px;">Rekapitulasi komisi investor dan hak outlet per periode</p>
            </div>
        </div>

        <!-- Sleek Pill Filter Bar -->
        <div class="filter-pill-container d-inline-flex align-items-center gap-2 mt-2 mt-md-0">
            <span class="text-body-secondary small fw-semibold text-nowrap"><i class="fa-light fa-filter me-1 text-danger"></i>Filter:</span>
            <div class="d-inline-flex align-items-center gap-1">
                <select id="filterBulanBagiHasil" title="Pilih Bulan">
                    <option value="0" <?= ($selectedBulan === 0) ? 'selected' : ''; ?>>Semua Bulan</option>
                    <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                        <option value="<?= $mNum; ?>" <?= ($selectedBulan === $mNum) ? 'selected' : ''; ?>>
                            <?= $mName; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="text-body-secondary opacity-50">/</span>
                <select id="filterTahunBagiHasil" title="Pilih Tahun">
                    <option value="0" <?= ($selectedTahun === 0) ? 'selected' : ''; ?>>Semua Tahun</option>
                    <?php foreach ($availableYears as $yVal) : ?>
                        <option value="<?= $yVal; ?>" <?= ($selectedTahun === $yVal) ? 'selected' : ''; ?>>
                            <?= $yVal; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill" style="font-size: 11px;">
                        <i class="fa-solid fa-store me-1"></i> <?= $countOutlet; ?> Outlet
                    </span>
                </div>
                <h2 class="fw-bold text-white mb-1 fs-3 fs-md-2">Bagi Hasil Periode <?= htmlspecialchars($periodeLabelStr); ?></h2>
                <p class="text-white-50 small mb-0">Total akumulasi komisi investor dan hak bersih outlet</p>
            </div>
            <div class="col-lg-5 col-12 text-lg-end">
                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 text-center text-lg-end">
                    <div class="text-white-50 small fw-semibold">Total Hak Bagi Hasil Investor</div>
                    <div class="fs-2 fw-extrabold text-warning">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></div>
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
                    <small class="text-body-secondary micro-text d-block">Total seluruh outlet</small>
                </div>
            </div>
        </div>

        <!-- 2. Potongan Global 10% -->
        <div class="col-6 col-xl-3">
            <div class="box-stat-bagi-hasil box-stat-potongan h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <span class="text-danger text-uppercase card-stat-title-full">Potongan Global (10%)</span>
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0 stat-icon-circle-sm">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                </div>
                <div>
                    <div class="fs-6 fs-md-4 fw-extrabold text-danger mb-1">Rp <?= number_format($totPotongan10, 0, ',', '.'); ?></div>
                    <small class="text-body-secondary micro-text d-block">Total potongan komisi</small>
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
                    <div class="fs-6 fs-md-4 fw-extrabold text-success mb-1">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></div>
                    <small class="text-success micro-text d-block"><i class="fa-solid fa-arrow-trend-up me-1"></i>Hak bersih investor</small>
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
                    <div class="fs-6 fs-md-4 fw-extrabold text-warning mb-1">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></div>
                    <small class="text-body-secondary micro-text d-block">Bagian milik outlet</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown Table Per Outlet (Sleek Theme-Adaptive Card Container) -->
    <div class="card border border-body-subtle shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-body-emphasis mb-0 fs-6">
                    <i class="text-danger"></i>Rincian Pembagian Hak Per Outlet (<?= htmlspecialchars($periodeLabelStr); ?>)
                </h5>
                <p class="text-body-secondary small mb-0">Rincian omzet, nominal potongan 10%, serta hak investor & outlet</p>
            </div>
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">
                <i class="fa-solid fa-calculator me-1"></i> Rekap Final
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-group-divider bg-body-secondary">
                        <tr class="text-uppercase small text-body-secondary">
                            <th class="py-3 ps-3 text-center fw-bold" style="width: 40px;">No</th>
                            <th class="py-3 px-3 fw-bold">Kode & Nama Outlet</th>
                            <th class="py-3 px-3 text-end fw-bold">Total Omzet (100%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-danger">Potongan (10%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-success">Hak Investor (50%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-warning">Hak Outlet (50%)</th>
                            <th class="py-3 px-3 text-end fw-bold">Bersih Outlet Total</th>
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
                                        <small class="text-body-secondary font-monospace"><?= htmlspecialchars($r['kode_outlet']); ?></small>
                                    </td>
                                    <td class="py-3 px-3 text-end fw-bold text-body-emphasis">Rp <?= number_format($r['total_omzet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-bold text-danger">Rp <?= number_format($r['potongan_10'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-extrabold text-success fs-6">Rp <?= number_format($r['hak_investor'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-extrabold text-warning fs-6">Rp <?= number_format($r['hak_outlet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-bold text-body-emphasis">Rp <?= number_format($r['total_bersih_outlet'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center text-body-secondary py-5">
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
                                <td class="py-3 px-3 text-end text-danger fs-6">Rp <?= number_format($totPotongan10, 0, ',', '.'); ?></td>
                                <td class="py-3 px-3 text-end text-success fs-5">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></td>
                                <td class="py-3 px-3 text-end text-warning fs-5">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></td>
                                <td class="py-3 px-3 text-end text-body-emphasis fs-6">Rp <?= number_format($totOmzet - $totHakInvestor, 0, ',', '.'); ?></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Filter Bulan & Filter Tahun Dropdown Change Handlers
    function applyBagiHasilFilters() {
        const bVal = $('#filterBulanBagiHasil').val();
        const yVal = $('#filterTahunBagiHasil').val();
        window.location.href = '<?= SystemInfo::app('CLIENT_URL'); ?>/bagi-hasil?bulan=' + bVal + '&tahun=' + yVal;
    }

    $('#filterBulanBagiHasil, #filterTahunBagiHasil').on('change', function() {
        applyBagiHasilFilters();
    });
});
</script>
