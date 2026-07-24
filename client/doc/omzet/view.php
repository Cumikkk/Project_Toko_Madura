<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? 0);

// Array nama bulan Bahasa Indonesia
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Get Outlet Record for Logged-In User
$resOutlet = $db->query("SELECT o.*, i.alamat_investor, u_inv.nama_lengkap as nama_investor FROM outlet o LEFT JOIN investor i ON o.id_investor = i.id_investor LEFT JOIN users u_inv ON i.id_users = u_inv.id_users WHERE o.id_users = {$userId} LIMIT 1");
$outlet = $resOutlet ? $resOutlet->fetch_assoc() : null;

// Get Current Global Cut Percentage from pengaturan_sistem
$resGlobal = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'potongan_global' LIMIT 1");
$presentaseGlobal = 10.00;
if ($resGlobal && $rowGlobal = $resGlobal->fetch_assoc()) {
    $presentaseGlobal = (float)$rowGlobal['nilai'];
}

$activeTab = $_GET['tab'] ?? 'input';

// Separate Month & Year Filter
$selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$laporanList = [];
$totalOmzet = 0;
$totalHariInput = 0;
$availableYears = [];

if ($outlet) {
    $idOutlet = (int)$outlet['id_outlet'];

    // Fetch distinct years available in database
    $resYears = $db->query("SELECT DISTINCT YEAR(periode_laporan) as y_periode FROM laporan_omzet WHERE id_outlet = {$idOutlet} ORDER BY y_periode DESC");
    if ($resYears) {
        while ($yRow = $resYears->fetch_assoc()) {
            $availableYears[] = (int)$yRow['y_periode'];
        }
    }
    if (!in_array((int)date('Y'), $availableYears)) {
        array_unshift($availableYears, (int)date('Y'));
    }

    // Build WHERE clause based on separate Month & Year filter
    $whereConditions = ["id_outlet = {$idOutlet}"];
    if ($selectedBulan > 0) {
        $whereConditions[] = "MONTH(periode_laporan) = {$selectedBulan}";
    }
    if ($selectedTahun > 0) {
        $whereConditions[] = "YEAR(periode_laporan) = {$selectedTahun}";
    }
    $whereSql = implode(" AND ", $whereConditions);

    // Determine last day of selected month/year for checking if end of month is reached
    $checkBulan = ($selectedBulan > 0) ? $selectedBulan : (int)date('n');
    $checkTahun = ($selectedTahun > 0) ? $selectedTahun : (int)date('Y');
    $lastDayDateStr = sprintf('%04d-%02d-%02d', $checkTahun, $checkBulan, cal_days_in_month(CAL_GREGORIAN, $checkBulan, $checkTahun));

    // Check if last day of this month is submitted
    $chkLastDay = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$lastDayDateStr}' LIMIT 1");
    $isLastDayDone = ($chkLastDay && $chkLastDay->num_rows > 0);

    // Fetch reports
    $sqlLaporan = "SELECT * FROM laporan_omzet WHERE {$whereSql} ORDER BY periode_laporan DESC, id_laporan DESC";
    $resLaporan = $db->query($sqlLaporan);
    if ($resLaporan) {
        while ($row = $resLaporan->fetch_assoc()) {
            $laporanList[] = $row;
            $totalOmzet += (float)$row['omzet'];
        }
    }
    $totalHariInput = count($laporanList);
}

// Calculate 10% monthly cut ONLY IF last day submitted
$totalPotonganBulanan = $isLastDayDone ? round($totalOmzet * ($presentaseGlobal / 100), 2) : 0.00;
$totalBersihOutlet = $totalOmzet - $totalPotonganBulanan;

$periodeLabelStr = ($selectedBulan > 0 ? ($bulanIndo[$selectedBulan] ?? '') . ' ' : '') . ($selectedTahun > 0 ? $selectedTahun : '');
if ($selectedBulan === 0 && $selectedTahun === 0) {
    $periodeLabelStr = 'Semua Periode';
}
?>

<style>
/* Auto Theme Adaptive Variables & Controls */
.custom-tab-container {
    background-color: var(--bs-tertiary-bg, #f8fafc) !important;
    border: 1px solid var(--bs-border-color, #e2e8f0) !important;
    border-radius: 14px;
    padding: 5px;
    display: inline-flex;
    align-items: center;
}
.custom-tab-container .nav-pills {
    margin: 0;
    padding: 0;
    width: 100%;
    display: flex;
    flex-wrap: nowrap;
    gap: 4px;
}
.custom-tab-container .nav-item {
    margin: 0;
}
.custom-tab-container .nav-link {
    color: var(--bs-body-color, #475569) !important;
    background-color: transparent !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    padding: 10px 22px !important;
    border-radius: 10px !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    white-space: nowrap !important;
    border: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.custom-tab-container .nav-link:hover {
    color: #7D0A0A !important;
    background-color: rgba(125, 10, 10, 0.08) !important;
}
.custom-tab-container .nav-link.active,
.custom-tab-container .nav-link.active:hover,
.custom-tab-container .nav-link.active:focus,
.custom-tab-container .nav-link.active:active {
    background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(125, 10, 10, 0.3) !important;
}

/* Premium Theme Adaptive Rekapitulasi Banner */
.rekap-card-grand {
    background-color: var(--bs-card-bg, #ffffff);
    border-radius: 16px;
    border: 1px solid var(--bs-border-color, #edf2f7);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}
.rekap-item-box {
    padding: 16px 20px;
    border-radius: 14px;
    transition: all 0.25s ease;
}
.rekap-box-omzet {
    background: rgba(25, 135, 84, 0.08);
    border: 1px solid rgba(25, 135, 84, 0.25);
    color: var(--bs-body-color);
}
.rekap-box-potongan {
    background: rgba(220, 53, 69, 0.08);
    border: 1px solid rgba(220, 53, 69, 0.25);
    color: var(--bs-body-color);
}
.rekap-box-bersih {
    background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);
    border: 1px solid #7D0A0A;
    color: #ffffff !important;
}

/* Sleek Clickable Date Input Field Styling */
.date-input-custom-group .input-group-text {
    background-color: rgba(125, 10, 10, 0.08) !important;
    border-color: var(--bs-border-color, #dee2e6) !important;
    color: #7D0A0A !important;
    font-size: 18px;
    padding-left: 16px;
    padding-right: 16px;
    cursor: pointer;
}
.date-input-custom-group .form-control {
    border-color: var(--bs-border-color, #dee2e6) !important;
    color: var(--bs-body-color) !important;
    background-color: var(--bs-body-bg) !important;
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: pointer;
}
.date-input-custom-group .form-control:focus {
    border-color: #7D0A0A !important;
    box-shadow: 0 0 0 0.25rem rgba(125, 10, 10, 0.15) !important;
}

@media (max-width: 575.98px) {
    .custom-tab-container {
        width: 100%;
        display: flex;
    }
    .custom-tab-container .nav-pills {
        width: 100%;
    }
    .custom-tab-container .nav-item {
        flex: 1;
    }
    .custom-tab-container .nav-link {
        font-size: 13px !important;
        padding: 9px 8px !important;
        width: 100% !important;
    }
    .rekap-item-box {
        padding: 12px 14px;
    }
}
</style>

<div class="main-content-inner py-3 py-md-4">
    <?php if (!$outlet) : ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-4 text-center">
            <i class="fa-light fa-circle-exclamation text-warning mb-3" style="font-size: 50px;"></i>
            <h4 class="fw-bold text-body-emphasis mb-2">Akun Belum Terhubung dengan Outlet</h4>
            <p class="text-body-secondary mb-0">Akun Anda belum memiliki data outlet terdaftar di sistem. Mohon hubungi pihak Investor untuk melakukan pendaftaran outlet Anda.</p>
        </div>
    <?php else : ?>

        <!-- Header Banner Card (Maroon Gradient - Text White Always Contrast) -->
        <div class="row mb-3 mb-md-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                    <div class="card-body p-3 p-md-5">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-8 col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-store me-1"></i> Outlet Panel
                                    </span>
                                </div>
                                <h2 class="fw-bold mb-1 text-white fs-3 fs-md-2"><?= htmlspecialchars($outlet['nama_outlet']); ?></h2>
                                <p class="text-white-50 small mb-0">
                                    <i class="fa-light fa-tag me-1"></i>Kode: <strong><?= htmlspecialchars($outlet['kode_outlet']); ?></strong> 
                                    <span class="mx-1">•</span> 
                                    <i class="fa-light fa-user me-1"></i>Investor: <strong><?= htmlspecialchars($outlet['nama_investor'] ?? 'Investor'); ?></strong>
                                </p>
                            </div>
                            <div class="col-lg-4 col-md-5 text-md-end text-start">
                                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 text-center text-md-end">
                                    <div class="text-white-50 small fw-semibold">Monitoring: <strong class="text-white"><?= htmlspecialchars($periodeLabelStr); ?></strong></div>
                                    <div class="fs-4 fw-bold text-warning">Rp <?= number_format($totalOmzet, 0, ',', '.'); ?></div>
                                    <div class="text-white-50 small">Total Akumulasi Omzet Kotor</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Bar & Filter Periode (Auto Theme Adaptive Controls) -->
        <div class="card border border-body-subtle shadow-sm mb-3 mb-md-4" style="border-radius: 16px;">
            <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Navigation Tabs (Sleek Side-by-Side Control) -->
                <div class="custom-tab-container shadow-sm">
                    <ul class="nav nav-pills" id="omzetNavTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= ($activeTab !== 'riwayat') ? 'active' : ''; ?>" id="btnTabInput" data-bs-toggle="pill" data-bs-target="#paneInputOmzet" type="button" role="tab">
                                <i class="fa-light fa-plus-circle me-2"></i>Input Omzet Harian
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= ($activeTab === 'riwayat') ? 'active' : ''; ?>" id="btnTabRiwayat" data-bs-toggle="pill" data-bs-target="#paneRiwayatOmzet" type="button" role="tab">
                                <i class="fa-light fa-clock-rotate-left me-2"></i>Riwayat & Rekap
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Period Filter Dropdowns (Filter Bulan & Filter Tahun Terpisah) -->
                <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-sm-auto">
                    <span class="text-body-secondary small fw-semibold d-none d-sm-inline"><i class="fa-light fa-filter me-1 text-danger"></i>Filter Periode:</span>
                    <div class="d-flex gap-2 w-100 w-sm-auto">
                        <select class="form-select form-select-sm rounded-pill border-danger-subtle fw-semibold text-body shadow-sm bg-body" id="filterBulan" style="min-width: 130px;">
                            <option value="0" <?= ($selectedBulan === 0) ? 'selected' : ''; ?>>Semua Bulan</option>
                            <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                <option value="<?= $mNum; ?>" <?= ($selectedBulan === $mNum) ? 'selected' : ''; ?>>
                                    <?= $mName; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select form-select-sm rounded-pill border-danger-subtle fw-semibold text-body shadow-sm bg-body" id="filterTahun" style="min-width: 100px;">
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
        </div>

        <!-- Summary Metrics Cards (Theme-Adaptive Text Colors) -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                        <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                            <i class="fa-light fa-calendar-check fs-5 fs-md-4"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-body-secondary small fw-semibold">Total Hari Input</div>
                            <div class="fs-5 fs-md-4 fw-bold text-body-emphasis mb-0"><?= number_format($totalHariInput, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Hari</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-4">
                <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                        <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                            <i class="fa-light fa-money-bill-trend-up fs-5 fs-md-4"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-body-secondary small fw-semibold">Total Omzet (<?= htmlspecialchars($periodeLabelStr); ?>)</div>
                            <div class="fs-6 fs-md-5 fw-bold text-success mb-0">Rp <?= number_format($totalOmzet, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                        <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);">
                            <i class="fa-light fa-wallet fs-5 fs-md-4 text-dark"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-body-secondary small fw-semibold">Total Bersih Toko</div>
                            <div class="fs-6 fs-md-5 fw-bold text-body-emphasis mb-0">Rp <?= number_format($totalBersihOutlet, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Panes -->
        <div class="tab-content" id="omzetTabContent">
            
            <!-- ========================================================================= -->
            <!-- TAB 1: FORM INPUT OMZET HARIAN -->
            <!-- ========================================================================= -->
            <div class="tab-pane fade <?= ($activeTab !== 'riwayat') ? 'show active' : ''; ?>" id="paneInputOmzet" role="tabpanel">
                <div class="row g-3 g-md-4">
                    <!-- Form Input Column -->
                    <div class="col-lg-7 col-xl-8">
                        <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                            <div class="card-header bg-transparent border-0 pt-4 px-3 px-md-4 pb-0">
                                <h5 class="fw-bold mb-1 fs-5 text-body-emphasis"><i class="fa-solid fa-file-circle-plus me-2 text-danger"></i>Form Input Omzet Harian Toko</h5>
                                <p class="text-body-secondary small mb-0">Input total omzet harian operasional toko Anda.</p>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <form id="formInputOmzet" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    
                                    <!-- Sleek Calendar Icon & Instant Pop-up Datepicker Input Field -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-body-secondary required">Tanggal Input Omzet Harian</label>
                                        <div class="input-group date-input-custom-group">
                                            <span class="input-group-text border-end-0 rounded-start-3" title="Klik untuk pilih tanggal">
                                                <i class="fa-solid fa-calendar-days text-danger"></i>
                                            </span>
                                            <input type="date" name="tanggal_omzet" id="tanggal_omzet" class="form-control border-start-0 rounded-end-3" value="<?= date('Y-m-d'); ?>" required style="font-size: 15px;">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold small text-body-secondary required">Total Omzet Penjualan Harian</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-danger text-white fw-bold border-end-0">Rp</span>
                                            <input type="text" name="omzet" id="input_omzet_val" class="form-control amount-formatter fw-bold border-start-0 text-body-emphasis fs-4 bg-body" placeholder="0" autocomplete="off" required style="font-size: 18px;">
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger btn-lg rounded-pill fw-bold py-3 shadow">
                                            <i class="fa-solid fa-paper-plane me-2"></i> Simpan Omzet Harian
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Live Calculator Preview Card -->
                    <div class="col-lg-5 col-xl-4">
                        <div class="card border border-body-subtle shadow-sm bg-body-tertiary" style="border-radius: 16px;">
                            <div class="card-header bg-transparent border-0 pt-4 px-3 px-md-4 pb-0">
                                <h6 class="fw-bold mb-1 text-body-emphasis"><i class="fa-light fa-calculator me-2 text-danger"></i>Ringkasan Input Harian</h6>
                                <p class="text-body-secondary small mb-0">Rincian nominal omzet harian Anda.</p>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <div class="p-3 bg-body rounded-3 border border-body-subtle mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-body-secondary small">Input Omzet Hari Ini</span>
                                        <span class="fw-bold text-body-emphasis fs-6" id="calc_omzet_val">Rp 0</span>
                                    </div>
                                    <hr class="my-2 opacity-25">
                                    <div class="d-flex justify-content-between align-items-center">
                                       
                                        <span class="fw-bold text-primary small">Diakumulasi per Bulan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 2: RIWAYAT & REKAP PER PERIODE BULANAN -->
            <!-- ========================================================================= -->
            <div class="tab-pane fade <?= ($activeTab === 'riwayat') ? 'show active' : ''; ?>" id="paneRiwayatOmzet" role="tabpanel">
                <div class="card border border-body-subtle shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-3 px-md-4 pb-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h5 class="fw-bold mb-1 fs-5 text-body-emphasis"><i class="text-danger"></i>Riwayat Omzet Periode <?= htmlspecialchars($periodeLabelStr); ?></h5>
                            <p class="text-body-secondary small mb-0">Daftar penginputan omzet harian toko Anda</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Tombol Hapus Terpilih / Bulk Delete Button -->
                            <button type="button" id="btnDeleteSelected" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm d-none">
                                <i class="fa-solid fa-trash-can me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2 p-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 w-100" id="tableRiwayatOmzet">
                                <thead class="table-group-divider bg-body-secondary">
                                    <tr class="text-uppercase small text-body-secondary">
                                        <th class="ps-3" style="width: 40px;">
                                            <input type="checkbox" id="checkAllOmzet" class="form-check-input cursor-pointer" title="Pilih Semua">
                                        </th>
                                        <th style="width: 40px;">No</th>
                                        <th>Tanggal Omzet</th>
                                        <th>Waktu Input</th>
                                        <th>Nominal Omzet Harian</th>
                                        <th class="text-center pe-3" style="width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($laporanList)) : ?>
                                        <?php foreach ($laporanList as $index => $row) : ?>
                                            <?php 
                                                $omz = (float)$row['omzet'];
                                                $t = strtotime($row['periode_laporan']);
                                                $tglStr = date('d/m/Y', $t);
                                            ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <input type="checkbox" class="form-check-input check-item-omzet cursor-pointer" value="<?= $row['id_laporan']; ?>">
                                                </td>
                                                <td class="fw-bold text-body-secondary"><?= $index + 1; ?></td>
                                                <td>
                                                    <span class="fw-bold text-body-emphasis fs-6">
                                                        <i class="fa-solid fa-calendar-days text-danger me-1"></i>
                                                        <?= date('d M Y', $t); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-body-secondary">
                                                        <?= date('d/m/Y H:i', strtotime($row['waktu_input'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-body-emphasis fs-6">
                                                        Rp <?= number_format($omz, 0, ',', '.'); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center pe-3">
                                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                                        <button type="button" class="btn btn-sm btn-light border text-info btn-detail-laporan rounded-3 px-2 py-1" data-id="<?= $row['id_laporan']; ?>" title="Lihat Detail">
                                                            <i class="fa-light fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light border text-warning btn-edit-laporan rounded-3 px-2 py-1" data-id="<?= $row['id_laporan']; ?>" title="Edit Laporan">
                                                            <i class="fa-light fa-pen-to-square"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light border text-danger btn-delete-laporan rounded-3 px-2 py-1" data-id="<?= $row['id_laporan']; ?>" data-tgl="<?= $tglStr; ?>" title="Hapus Laporan">
                                                            <i class="fa-light fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="py-4">
                                                    <i class="fa-light fa-file-invoice-dollar text-body-secondary mb-3" style="font-size: 60px; opacity: 0.5;"></i>
                                                    <h5 class="fw-bold text-body-secondary mb-1">Belum Ada Omzet Harian Terinput</h5>
                                                    <p class="text-body-secondary small mb-3">Gunakan form input di tab sebelah untuk mendaftarkan omzet harian toko Anda.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- REKAPITULASI AKUMULASI OMZET BULANAN (Grand Summary Cards - Auto Theme) -->
                <!-- ========================================================================= -->
                <?php if (!empty($laporanList)) : ?>
                    <div class="rekap-card-grand p-3 p-md-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 border-body-subtle">
                            <div>
                                <h6 class="fw-bold text-body-emphasis mb-0 fs-6">
                                    <i class="text-danger"></i>Rekapitulasi Omzet & Bagi Hasil (<?= htmlspecialchars($periodeLabelStr); ?>)
                                </h6>
                                <p class="text-body-secondary small mb-0">Ringkasan total akumulasi omzet, potongan global, dan pendapatan bersih outlet</p>
                            </div>
                            <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-3 py-2">
                                <i class="fa-light fa-calculator me-1"></i> Rekap Final
                            </span>
                        </div>

                        <div class="row g-3">
                            <!-- 1. Total Omzet Sebulan -->
                            <div class="col-12 col-md-4">
                                <div class="rekap-item-box rekap-box-omzet h-100 d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="text-success small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Omzet Sebulan (100%)</div>
                                        <div class="fs-4 fw-extrabold text-success mt-1">Rp <?= number_format($totalOmzet, 0, ',', '.'); ?></div>
                                        <div class="text-body-secondary small mt-1"><i class="fa-light fa-circle-check me-1 text-success"></i>Dari <?= $totalHariInput; ?> hari penginputan</div>
                                    </div>
                                    <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-coins fs-4"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Potongan Global 10% Sebulan -->
                            <div class="col-12 col-md-4">
                                <div class="rekap-item-box rekap-box-potongan h-100 d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="text-danger small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Potongan Global (10%)</div>
                                        <div class="fs-4 fw-extrabold text-danger mt-1">
                                            <?php if ($isLastDayDone) : ?>
                                                - Rp <?= number_format($totalPotonganBulanan, 0, ',', '.'); ?>
                                            <?php else : ?>
                                                -
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-body-secondary small mt-1">
                                            <?php if ($isLastDayDone) : ?>
                                                <i class="fa-light fa-check me-1 text-danger"></i>Dipotong pada tanggal akhir bulan
                                            <?php else : ?>
                                                <i class="fa-light fa-clock me-1"></i>Belum dihitung (tunggu akhir bulan)
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="rounded-circle bg-danger text-white p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-percent fs-4"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Total Hak Bersih Outlet Sebulan (90%) -->
                            <div class="col-12 col-md-4">
                                <div class="rekap-item-box rekap-box-bersih h-100 d-flex align-items-center justify-content-between shadow-sm">
                                    <div>
                                        <div class="text-warning small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Hak Bersih Outlet (90%)</div>
                                        <div class="fs-4 fw-extrabold text-warning mt-1">Rp <?= number_format($totalBersihOutlet, 0, ',', '.'); ?></div>
                                        <div class="text-white-50 small mt-1"><i class="fa-light fa-wallet me-1 text-warning"></i>Siap dibagikan / disetor</div>
                                    </div>
                                    <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-wallet fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT LAPORAN OMZET (Auto Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditLaporan" tabindex="-1" aria-labelledby="modalEditLaporanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalEditLaporanLabel">
                    <i class="fa-light fa-pen-to-square me-2 text-warning"></i>Edit Input Omzet Harian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditLaporan" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_laporan" id="edit_id_laporan" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Tanggal Omzet</label>
                        <div class="input-group date-input-custom-group">
                            <span class="input-group-text border-end-0 rounded-start-3" title="Klik untuk pilih tanggal">
                                <i class="fa-solid fa-calendar-days text-danger"></i>
                            </span>
                            <input type="date" name="tanggal_omzet" id="edit_tanggal_omzet" class="form-control border-start-0 rounded-end-3" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Total Omzet Penjualan Harian</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-warning text-dark fw-bold border-end-0">Rp</span>
                            <input type="text" name="omzet" id="edit_omzet_val" class="form-control amount-formatter fw-bold border-start-0 text-body-emphasis fs-4 bg-body" placeholder="0" autocomplete="off" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Omzet Harian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL LAPORAN OMZET (Auto Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDetailLaporan" tabindex="-1" aria-labelledby="modalDetailLaporanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalDetailLaporanLabel">
                    <i class="fa-light fa-file-invoice-dollar me-2 text-info"></i>Rincian Omzet Harian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="detailLaporanLoading" class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="detailLaporanContent" class="d-none">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success p-3 mb-2" style="width: 64px; height: 64px;">
                            <i class="fa-light fa-money-bill-trend-up fs-2"></i>
                        </div>
                        <h3 class="fw-bold mb-1 text-success" id="det_omzet_head">Rp 0</h3>
                        <span class="badge bg-danger px-3 py-1 rounded-pill fs-6" id="det_periode_head">Tanggal: -</span>
                    </div>

                    <div class="list-group list-group-flush rounded-3 border border-body-subtle mb-3">
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-solid fa-calendar-days me-2 text-danger"></i>Tanggal Omzet Harian</span>
                            <span class="fw-bold text-body-emphasis" id="det_periode">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-clock me-2 text-body-secondary"></i>Waktu Penginputan</span>
                            <span class="fw-semibold text-body-emphasis" id="det_waktu">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const ACTION_URL = '<?= SystemInfo::app('CLIENT_URL'); ?>/doc/omzet/action.php';

    // Instant Datepicker Pop-up Handler when clicking icon or date input
    $(document).on('click', '.date-input-custom-group .input-group-text, .date-input-custom-group .form-control', function() {
        const group = $(this).closest('.date-input-custom-group');
        const dateInput = group.find('input[type="date"]')[0];
        if (dateInput) {
            if (typeof dateInput.showPicker === 'function') {
                try {
                    dateInput.showPicker();
                } catch(err) {
                    dateInput.focus();
                }
            } else {
                dateInput.focus();
            }
        }
    });

    // Filter Bulan & Filter Tahun Dropdown Change Handlers
    function applyPeriodFilters() {
        const bVal = $('#filterBulan').val();
        const yVal = $('#filterTahun').val();
        window.location.href = '<?= SystemInfo::app('CLIENT_URL'); ?>/omzet?tab=riwayat&bulan=' + bVal + '&tahun=' + yVal;
    }

    $('#filterBulan, #filterTahun').on('change', function() {
        applyPeriodFilters();
    });

    // Helper: Format Rupiah Live Calculator
    function updateLiveCalc() {
        const rawVal = $('#input_omzet_val').val().replace(/[^\d]/g, '');
        const omzetNum = parseFloat(rawVal) || 0;
        $('#calc_omzet_val').text('Rp ' + new Intl.NumberFormat('id-ID').format(omzetNum));
    }

    // Bind Keyup on input_omzet_val
    $('#input_omzet_val').on('input keyup change', function() {
        updateLiveCalc();
    });

    // Checkbox Master & Item Selection Handlers
    function updateBulkDeleteButton() {
        const checkedCount = $('.check-item-omzet:checked').length;
        const totalCount = $('.check-item-omzet').length;
        
        if (checkedCount > 0) {
            $('#selectedCount').text(checkedCount);
            $('#btnDeleteSelected').removeClass('d-none');
        } else {
            $('#btnDeleteSelected').addClass('d-none');
        }

        if (totalCount > 0 && checkedCount === totalCount) {
            $('#checkAllOmzet').prop('checked', true);
        } else {
            $('#checkAllOmzet').prop('checked', false);
        }
    }

    $('#checkAllOmzet').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.check-item-omzet').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    $(document).on('change', '.check-item-omzet', function() {
        updateBulkDeleteButton();
    });

    // Bulk Delete Selected Handler
    $('#btnDeleteSelected').on('click', function() {
        const selectedIds = [];
        $('.check-item-omzet:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire('Peringatan', 'Pilih sekurang-kurangnya satu data omzet.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Hapus Data Terpilih?',
            text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} data omzet yang dipilih?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Ya, Hapus (${selectedIds.length})`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus Data Terpilih...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: ACTION_URL,
                    type: 'POST',
                    data: { action: 'delete_selected', ids: selectedIds },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus data omzet terpilih.', 'error');
                    }
                });
            }
        });
    });

    // 1. Submit Form Input Omzet Harian
    $('#formInputOmzet').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Menyimpan Omzet Harian...');

        $.ajax({
            url: ACTION_URL,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Simpan Omzet Harian');
                if (res.success) {
                    form[0].reset();
                    $('#tanggal_omzet').val('<?= date('Y-m-d'); ?>');
                    updateLiveCalc();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Omzet Harian Tersimpan!',
                        text: res.message,
                        confirmButtonText: 'Lihat Rekap'
                    }).then(() => {
                        window.location.href = '<?= SystemInfo::app('CLIENT_URL'); ?>/omzet?tab=riwayat';
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Simpan Omzet Harian');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat mengirim laporan.', 'error');
            }
        });
    });

    // 2. Fetch Detail for Edit
    $(document).on('click', '.btn-edit-laporan', function() {
        const idLaporan = $(this).data('id');
        
        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_laporan: idLaporan },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#edit_id_laporan').val(res.data.id_laporan);
                    $('#edit_tanggal_omzet').val(res.data.tgl_formatted);
                    $('#edit_omzet_val').val(new Intl.NumberFormat('id-ID').format(res.data.omzet));
                    $('#modalEditLaporan').modal('show');
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal mengambil data laporan.', 'error');
            }
        });
    });

    // 3. Submit Edit Laporan
    $('#formEditLaporan').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating...');

        $.ajax({
            url: ACTION_URL,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Update Omzet Harian');
                if (res.success) {
                    $('#modalEditLaporan').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Update Omzet Harian');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat memperbarui data.', 'error');
            }
        });
    });

    // 4. Fetch Detail for View
    $(document).on('click', '.btn-detail-laporan', function() {
        const idLaporan = $(this).data('id');
        $('#detailLaporanLoading').removeClass('d-none');
        $('#detailLaporanContent').addClass('d-none');
        $('#modalDetailLaporan').modal('show');

        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_laporan: idLaporan },
            dataType: 'json',
            success: function(res) {
                $('#detailLaporanLoading').addClass('d-none');
                if (res.success) {
                    const omzetFormatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(res.data.omzet);
                    $('#det_omzet_head').text(omzetFormatted);
                    $('#det_periode_head').text('Tanggal: ' + res.data.tgl_indo);
                    $('#det_periode').text(res.data.tgl_indo);
                    $('#det_waktu').text(res.data.waktu_input);
                    $('#detailLaporanContent').removeClass('d-none');
                } else {
                    $('#modalDetailLaporan').modal('hide');
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                $('#modalDetailLaporan').modal('hide');
                Swal.fire('Error', 'Gagal mengambil rincian omzet harian.', 'error');
            }
        });
    });

    // 5. Delete Single Laporan with SweetAlert2
    $(document).on('click', '.btn-delete-laporan', function() {
        const idLaporan = $(this).data('id');
        const tglStr = $(this).data('tgl');

        Swal.fire({
            title: 'Hapus Omzet Harian?',
            text: `Apakah Anda yakin ingin menghapus omzet harian tanggal ${tglStr}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: ACTION_URL,
                    type: 'POST',
                    data: { action: 'delete', id_laporan: idLaporan },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus omzet harian.', 'error');
                    }
                });
            }
        });
    });
});
</script>
