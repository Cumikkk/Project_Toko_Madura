<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Filter Parameters
$search             = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$whereConds = ["(i.id_master = {$userId} OR i.id_master IS NULL)"];

if (!empty($search)) {
    $safeSearch = $db->real_escape_string($search);
    $whereConds[] = "(o.nama_outlet LIKE '%{$safeSearch}%' OR u_inv.nama_lengkap LIKE '%{$safeSearch}%' OR u_out.kecamatan LIKE '%{$safeSearch}%' OR u_out.alamat LIKE '%{$safeSearch}%')";
}

// Build JOIN condition for laporan_omzet
$joinLoConds = [];
if (!empty($selectedTglMulai) && !empty($selectedTglSelesai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $joinLoConds[] = "lo.periode_laporan BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
} elseif (!empty($selectedTglMulai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $joinLoConds[] = "lo.periode_laporan >= '{$safeMulai}'";
} elseif (!empty($selectedTglSelesai)) {
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $joinLoConds[] = "lo.periode_laporan <= '{$safeSelesai}'";
} else {
    if ($selectedBulan > 0) {
        $joinLoConds[] = "MONTH(lo.periode_laporan) = {$selectedBulan}";
    }
    if ($selectedTahun > 0) {
        $joinLoConds[] = "YEAR(lo.periode_laporan) = {$selectedTahun}";
    }
}

$joinLoSql = !empty($joinLoConds) ? " AND " . implode(" AND ", $joinLoConds) : "";
$whereSql = "WHERE " . implode(" AND ", $whereConds);

// Pagination setup
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Count Total Outlets for Master matching filter
$sqlCount = "
    SELECT COUNT(DISTINCT o.id_outlet) as total 
    FROM outlet o
    JOIN investor i ON i.id_investor = o.id_investor
    JOIN users u_inv ON u_inv.id_users = i.id_users
    LEFT JOIN users u_out ON u_out.id_users = o.id_users
    {$whereSql}
";
$resTotal = $db->query($sqlCount);
$totalRecords = ($resTotal && $rowT = $resTotal->fetch_assoc()) ? (int)$rowT['total'] : 0;
$totalPages   = ($totalRecords > 0) ? (int)ceil($totalRecords / $limit) : 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Fetch Available Years for Filter
$availableYears = [];
$resYears = $db->query("
    SELECT DISTINCT YEAR(lo.periode_laporan) as y_periode 
    FROM laporan_omzet lo 
    JOIN outlet o ON o.id_outlet = lo.id_outlet 
    JOIN investor i ON i.id_investor = o.id_investor 
    WHERE (i.id_master = {$userId} OR i.id_master IS NULL) 
    ORDER BY y_periode DESC
");
if ($resYears) {
    while ($yRow = $resYears->fetch_assoc()) {
        if (!empty($yRow['y_periode'])) {
            $availableYears[] = (int)$yRow['y_periode'];
        }
    }
}
if (!in_array((int)date('Y'), $availableYears)) {
    array_unshift($availableYears, (int)date('Y'));
}

// Overall Totals for Metric Summary Cards (across all filtered outlets)
$sqlSummaryTot = "
    SELECT 
        IFNULL(SUM(lo.omzet), 0) as grand_omzet,
        IFNULL(SUM(lo.nominal_potongan), 0) as grand_potongan
    FROM outlet o
    JOIN investor i ON i.id_investor = o.id_investor
    JOIN users u_inv ON u_inv.id_users = i.id_users
    LEFT JOIN users u_out ON u_out.id_users = o.id_users
    JOIN laporan_omzet lo ON (lo.id_outlet = o.id_outlet {$joinLoSql})
    {$whereSql}
";
$resSumTot = $db->query($sqlSummaryTot);
$grandOmzet = 0;
$grandPotongan = 0;
$grandHakMaster = 0;
if ($resSumTot && $rSum = $resSumTot->fetch_assoc()) {
    $grandOmzet = (float)$rSum['grand_omzet'];
    $grandPotongan = (float)$rSum['grand_potongan'];
    $grandHakMaster = ($grandOmzet - $grandPotongan) * 0.05;
}

// Fetch Paginated Master Profit Report per outlet
$sqlKeuntungan = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        u_out.kecamatan,
        u_out.alamat as alamat_outlet,
        u_inv.nama_lengkap as nama_investor,
        5.00 as persen_master,
        IFNULL(SUM(lo.omzet), 0) as total_omzet,
        IFNULL(SUM(lo.nominal_potongan), 0) as total_potongan
    FROM outlet o
    JOIN investor i ON i.id_investor = o.id_investor
    JOIN users u_inv ON u_inv.id_users = i.id_users
    LEFT JOIN users u_out ON u_out.id_users = o.id_users
    LEFT JOIN laporan_omzet lo ON (lo.id_outlet = o.id_outlet {$joinLoSql})
    {$whereSql}
    GROUP BY o.id_outlet
    ORDER BY o.id_outlet DESC
    LIMIT {$limit} OFFSET {$offset}
";

$reports = $db->query($sqlKeuntungan);
$reportList = [];
$totalOmzetPage = 0;
$totalPotonganPage = 0;
$totalHakPage = 0;

if ($reports && $reports->num_rows > 0) {
    while ($row = $reports->fetch_assoc()) {
        $reportList[] = $row;
        $omzet = (float)$row['total_omzet'];
        $potongan = (float)$row['total_potongan'];
        $omzetBersih = $omzet - $potongan;
        $persenMaster = (float)$row['persen_master'];
        $hakMaster = $omzetBersih * ($persenMaster / 100.0);

        $totalOmzetPage += $omzet;
        $totalPotonganPage += $potongan;
        $totalHakPage += $hakMaster;
    }
}

function buildKeuntunganPageUrl($pageNum, $selectedTglMulai = '', $selectedTglSelesai = '', $selectedBulan = 0, $selectedTahun = 0, $search = '') {
    $params = ['page' => $pageNum];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($selectedTglMulai)) $params['tgl_mulai'] = $selectedTglMulai;
    if (!empty($selectedTglSelesai)) $params['tgl_selesai'] = $selectedTglSelesai;
    if ($selectedBulan > 0) $params['bulan'] = $selectedBulan;
    if ($selectedTahun > 0) $params['tahun'] = $selectedTahun;
    return SystemInfo::app('CLIENT_URL') . '/keuntungan-master?' . http_build_query($params);
}

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<div class="main-content-inner py-3 py-md-4">
    <!-- Header Banner Card -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-12">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-crown text-warning me-1"></i> Master Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Keuntungan Master Owner</h2>
                            <p class="text-white-50 small mb-0">Kalkulasi otomatis porsi komisi 5% Master Owner berdasarkan omzet bersih seluruh outlet di bawah naungan Master Owner.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards (3 Cards) -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-md-4 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <i class="fa-solid fa-chart-line fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Akumulasi Omzet Kotor</div>
                        <div class="fs-5 fw-bold text-primary mb-0">Rp <?= number_format($grandOmzet, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                        <i class="fa-solid fa-receipt fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Total Potongan (10%)</div>
                        <div class="fs-5 fw-bold text-danger mb-0">Rp <?= number_format($grandPotongan, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-solid fa-sack-dollar fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Hak Komisi Master (5%)</div>
                        <div class="fs-5 fw-bold text-success mb-0">Rp <?= number_format($grandHakMaster, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                <!-- Header with Live Search & Filter Button -->
                <div class="card-header bg-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-body-emphasis mb-1 fs-6">
                            <i class="fa-solid fa-file-invoice-dollar me-2 text-danger"></i>Laporan Keuntungan Komisi Master
                            <?php if (!empty($selectedTglMulai) || !empty($selectedTglSelesai)) : ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2 fw-bold" style="font-size: 10px;">
                                    <i class="fa-solid fa-calendar-range me-1"></i>
                                    <?= !empty($selectedTglMulai) ? date('d/m/Y', strtotime($selectedTglMulai)) : 'Awal'; ?> s/d <?= !empty($selectedTglSelesai) ? date('d/m/Y', strtotime($selectedTglSelesai)) : 'Akhir'; ?>
                                </span>
                            <?php elseif ($selectedBulan > 0 || $selectedTahun > 0) : ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2 fw-bold" style="font-size: 10px;">
                                    <i class="fa-solid fa-calendar-day me-1"></i>
                                    <?= ($selectedBulan > 0) ? $bulanIndo[$selectedBulan] : ''; ?> <?= ($selectedTahun > 0) ? $selectedTahun : ''; ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                        <p class="text-body-secondary small mb-0">Pantau rincian komisi 5% Master Owner berdasarkan omzet seluruh outlet</p>
                    </div>

                    <!-- Live Search & Tombol Filter Utama -->
                    <div class="d-flex align-items-center gap-2 flex-nowrap ms-auto">
                        <!-- Live Search Input Box -->
                        <div class="input-group input-group-sm" style="width: 180px; sm:width: 220px;">
                            <span class="input-group-text bg-body border-danger-subtle rounded-start-pill text-body-secondary"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input type="text" id="liveSearchKeuntungan" class="form-control border-danger-subtle rounded-end-pill fw-semibold text-body bg-body shadow-sm" value="<?= htmlspecialchars($search); ?>" placeholder="Cari outlet / investor..." title="Live Search Keuntungan Master">
                        </div>

                        <!-- Tombol Filter Utama (Membuka Modal Filter Data) -->
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterKeuntungan">
                            <i class="fa-solid fa-filter me-1"></i> Filter Data
                        </button>
                    </div>
                </div>

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableKeuntunganMaster">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3 text-center" style="width: 50px;">No</th>
                                    <th>Nama Outlet</th>
                                    <th class="text-center">Investor Pemodal</th>
                                    <th class="text-end">Omzet Kotor</th>
                                    <th class="text-end">Potongan (10%)</th>
                                    <th class="text-end">Omzet Bersih</th>
                                    <th class="text-center">Bagian Master (%)</th>
                                    <th class="text-end pe-3">Hak Master (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (!empty($reportList)) : ?>
                                    <?php 
                                    $no = $offset + 1; 
                                    foreach ($reportList as $row) :
                                        $omzet = (float)$row['total_omzet'];
                                        $potongan = (float)$row['total_potongan'];
                                        $omzetBersih = $omzet - $potongan;
                                        $persenMaster = (float)$row['persen_master'];
                                        $hakMaster = $omzetBersih * ($persenMaster / 100.0);
                                    ?>
                                        <tr class="keuntungan-data-row">
                                            <td class="ps-3 text-center fw-bold text-body-secondary"><?= $no++ ?></td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6">
                                                    <i class="fa-solid fa-store text-danger me-1"></i><?= htmlspecialchars($row['nama_outlet']) ?>
                                                </div>
                                                <div class="text-body-secondary small mt-0.5 d-flex align-items-center gap-1.5 flex-wrap">
                                                    <?php if (!empty($row['alamat_outlet'])) : ?>
                                                        <span class="badge bg-light text-body-secondary border btn-detail-alamat-outlet shadow-xs" style="font-size: 11px; cursor: pointer;"
                                                              data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>"
                                                              data-kecamatan="<?= htmlspecialchars($row['kecamatan'] ?: '-', ENT_QUOTES, 'UTF-8') ?>"
                                                              data-alamat="<?= htmlspecialchars($row['alamat_outlet'] ?: '-', ENT_QUOTES, 'UTF-8') ?>"
                                                              title="Klik untuk lihat detail alamat">
                                                            <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($row['kecamatan'] ?: 'N/A') ?>
                                                        </span>
                                                    <?php else : ?>
                                                        <span class="badge bg-light text-body-secondary border" style="font-size: 11px;">
                                                            <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($row['kecamatan'] ?: 'N/A') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center fw-semibold text-body-emphasis">
                                                <?= htmlspecialchars($row['nama_investor']) ?>
                                            </td>
                                            <td class="text-end fw-bold text-primary">
                                                Rp <?= number_format($omzet, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-end text-danger fw-semibold">
                                                Rp <?= number_format($potongan, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-end fw-bold text-body-emphasis">
                                                Rp <?= number_format($omzetBersih, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-bold fs-12">
                                                    <?= number_format($persenMaster, 2, ',', '.') ?>%
                                                </span>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-success fs-6">
                                                Rp <?= number_format($hakMaster, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-body-secondary">
                                            <i class="fa-solid fa-file-circle-xmark fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                            Belum ada data omzet untuk kriteria filter terpilih.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-group-divider bg-body-tertiary fw-bold">
                                <tr>
                                    <td colspan="3" class="ps-3 text-end text-uppercase text-body-secondary">Subtotal Halaman Ini:</td>
                                    <td class="text-end text-primary fw-bold">Rp <?= number_format($totalOmzetPage, 0, ',', '.') ?></td>
                                    <td class="text-end text-danger fw-bold">Rp <?= number_format($totalPotonganPage, 0, ',', '.') ?></td>
                                    <td class="text-end text-body-emphasis fw-bold">Rp <?= number_format($totalOmzetPage - $totalPotonganPage, 0, ',', '.') ?></td>
                                    <td></td>
                                    <td class="text-end pe-3 text-success fw-bold fs-6">Rp <?= number_format($totalHakPage, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pagination Controls & Record Summary Footer -->
                    <?php if ($totalRecords > 0) : ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-body-subtle mt-2">
                            <div class="small text-body-secondary fw-semibold ms-1">
                                Menampilkan <span class="text-body-emphasis fw-bold"><?= ($totalRecords > 0) ? ($offset + 1) : 0; ?></span> - <span class="text-body-emphasis fw-bold"><?= min($offset + $limit, $totalRecords); ?></span> dari <span class="text-body-emphasis fw-bold"><?= $totalRecords; ?></span> outlet terdaftar
                            </div>

                            <?php if ($totalPages > 1) : ?>
                                <nav aria-label="Navigasi Halaman Keuntungan Master">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-start-pill text-body-emphasis px-3" href="<?= buildKeuntunganPageUrl($page - 1, $selectedTglMulai, $selectedTglSelesai, $selectedBulan, $selectedTahun, $search); ?>">
                                                <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                            </a>
                                        </li>

                                        <?php for ($p = 1; $p <= $totalPages; $p++) : ?>
                                            <li class="page-item <?= ($p === $page) ? 'active' : ''; ?>">
                                                <a class="page-link <?= ($p === $page) ? 'bg-danger border-danger text-white fw-bold' : 'text-body-emphasis'; ?>" href="<?= buildKeuntunganPageUrl($p, $selectedTglMulai, $selectedTglSelesai, $selectedBulan, $selectedTahun, $search); ?>"><?= $p; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-end-pill text-body-emphasis px-3" href="<?= buildKeuntunganPageUrl($page + 1, $selectedTglMulai, $selectedTglSelesai, $selectedBulan, $selectedTahun, $search); ?>">
                                                Next <i class="fa-solid fa-chevron-right ms-1"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: FILTER DATA KEUNTUNGAN MASTER -->
<div class="modal fade" id="modalFilterKeuntungan" tabindex="-1" aria-labelledby="modalFilterKeuntunganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg bg-body" style="border-radius: 20px;">
            <div class="modal-header border-bottom border-body-subtle py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="modal-title fw-extrabold text-body-emphasis mb-0 fs-6" id="modalFilterKeuntunganLabel">
                        <i class="fa-solid fa-filter me-2 text-danger"></i>Filter Laporan Keuntungan Master
                    </h6>
                    <small class="text-body-secondary" style="font-size: 11px;">Pilih kriteria pencarian dan periode laporan omzet</small>
                </div>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="<?= SystemInfo::app('CLIENT_URL'); ?>/keuntungan-master">
                <div class="modal-body p-4">
                    <!-- Search Input -->
                    <div class="mb-3">
                        <label for="filter_search" class="form-label small fw-bold text-body-secondary mb-1">Pencarian Nama Outlet / Investor / Kecamatan</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary border-body-subtle text-danger"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" id="filter_search" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold" value="<?= htmlspecialchars($search); ?>" placeholder="Nama outlet, investor, kecamatan...">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-body-secondary mb-0">
                            <i class="fa-regular fa-calendar-range me-1 text-danger"></i>Pilih Rentang Tanggal Periode
                        </label>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="filter_tgl_mulai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Mulai</label>
                            <div class="input-group input-group-sm cursor-pointer">
                                <span class="input-group-text bg-body-tertiary border-body-subtle text-danger"><i class="fa-solid fa-calendar-days"></i></span>
                                <input type="date" name="tgl_mulai" id="filter_tgl_mulai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglMulai); ?>">
                            </div>
                        </div>

                        <div class="col-6">
                            <label for="filter_tgl_selesai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Selesai</label>
                            <div class="input-group input-group-sm cursor-pointer">
                                <span class="input-group-text bg-body-tertiary border-body-subtle text-danger"><i class="fa-solid fa-calendar-days"></i></span>
                                <input type="date" name="tgl_selesai" id="filter_tgl_selesai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglSelesai); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Month & Year -->
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="filter_bulan" class="text-body-secondary small d-block mb-1">Bulan</label>
                            <select name="bulan" id="filter_bulan" class="form-select form-select-sm bg-body border-body-subtle text-body-emphasis fw-semibold">
                                <option value="0">Semua Bulan</option>
                                <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                    <option value="<?= $mNum; ?>" <?= ($selectedBulan == $mNum) ? 'selected' : ''; ?>><?= $mName; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="filter_tahun" class="text-body-secondary small d-block mb-1">Tahun</label>
                            <select name="tahun" id="filter_tahun" class="form-select form-select-sm bg-body border-body-subtle text-body-emphasis fw-semibold">
                                <option value="0">Semua Tahun</option>
                                <?php foreach ($availableYears as $y) : ?>
                                    <option value="<?= $y; ?>" <?= ($selectedTahun == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-body-subtle py-3 px-4 d-flex justify-content-between">
                    <a href="<?= SystemInfo::app('CLIENT_URL'); ?>/keuntungan-master" class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 12px;">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset Filter
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3 py-1.5 fw-semibold" data-bs-dismiss="modal" style="font-size: 12px;">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 py-1.5 fw-bold shadow-sm" style="background-color: #7D0A0A; border-color: #7D0A0A; font-size: 12px;">
                            <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

    // Instant Keyup Filter for Keuntungan Table
    $('#liveSearchKeuntungan').on('keyup search', function() {
        let val = $(this).val().toLowerCase().trim();
        $('.keuntungan-data-row').each(function() {
            let text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) > -1);
        });
    });

    $(document).on('click', '.btn-detail-alamat-outlet', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');
        let queryStr = encodeURIComponent((nama ? nama + ' ' : '') + alamat);
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;

        let html = `
            <div class="text-start fs-14">
                <div class="p-3 bg-light rounded-3 border mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-store text-danger me-2"></i>Nama Outlet</span>
                        <span class="fw-bold text-dark">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Kecamatan</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">${kec}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-body-secondary d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-2"></i>Alamat Lengkap Outlet:</span>
                        <div class="p-2.5 bg-white rounded border text-dark fw-semibold" style="font-size: 13.5px; line-height: 1.5;">
                            <a href="${mapsUrl}" target="_blank" class="text-primary text-decoration-underline fw-semibold" title="Klik untuk membuka Geotag Google Maps">
                                ${alamat} <i class="fas fa-external-link-alt ms-1 text-muted" style="font-size: 11px;"></i>
                            </a>
                            <small class="text-muted d-block text-start mt-1" style="font-size: 11px; font-weight: normal;"><i class="fas fa-info-circle me-1"></i>Klik teks alamat di atas untuk membuka lokasi di Google Maps</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            title: '<div class="fw-bold text-danger fs-5"><i class="fa-solid fa-building-user me-2"></i>Detail Lokasi Outlet</div>',
            html: html,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });
});
</script>
