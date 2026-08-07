<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$masterId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Filter Parameters
$search             = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$whereConds = ["id_master = {$masterId}"];

if (!empty($search)) {
    $safeSearch = $db->real_escape_string($search);
    $whereConds[] = "(periode LIKE '%{$safeSearch}%' OR catatan LIKE '%{$safeSearch}%' OR nominal LIKE '%{$safeSearch}%')";
}

if (!empty($selectedTglMulai) && !empty($selectedTglSelesai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConds[] = "DATE(tanggal_komisi) BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
} elseif (!empty($selectedTglMulai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $whereConds[] = "DATE(tanggal_komisi) >= '{$safeMulai}'";
} elseif (!empty($selectedTglSelesai)) {
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConds[] = "DATE(tanggal_komisi) <= '{$safeSelesai}'";
} else {
    if ($selectedBulan > 0) {
        $whereConds[] = "MONTH(tanggal_komisi) = {$selectedBulan}";
    }
    if ($selectedTahun > 0) {
        $whereConds[] = "YEAR(tanggal_komisi) = {$selectedTahun}";
    }
}

$whereSql = "WHERE " . implode(" AND ", $whereConds);

// Pagination setup for Client Komisi view
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Count Total Komisi Records for Master matching filter
$resTotalRec = $db->query("SELECT COUNT(id_komisi) as total FROM komisi_master {$whereSql}");
$totalRecords = ($resTotalRec && $rowT = $resTotalRec->fetch_assoc()) ? (int)$rowT['total'] : 0;
$totalPages   = ($totalRecords > 0) ? (int)ceil($totalRecords / $limit) : 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Fetch Komisi Statistics (matching filter)
$sqlStats = $db->query("
    SELECT 
        IFNULL(SUM(nominal), 0) as total_komisi,
        IFNULL(SUM(CASE WHEN MONTH(tanggal_komisi) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_komisi) = YEAR(CURRENT_DATE()) THEN nominal ELSE 0 END), 0) as komisi_bulan_ini
    FROM komisi_master 
    {$whereSql}
");
$stats = $sqlStats ? $sqlStats->fetch_assoc() : ['total_komisi' => 0, 'komisi_bulan_ini' => 0];

// Fetch distinct years of komisi transfers
$availableYears = [];
$resYears = $db->query("SELECT DISTINCT YEAR(tanggal_komisi) as y_periode FROM komisi_master WHERE id_master = {$masterId} ORDER BY y_periode DESC");
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

// Fetch Paginated Komisi List
$sqlList = "
    SELECT * 
    FROM komisi_master 
    {$whereSql}
    ORDER BY tanggal_komisi DESC, id_komisi DESC
    LIMIT {$limit} OFFSET {$offset}
";
$resKomisi = $db->query($sqlList);
$komisiList = [];
if ($resKomisi && $resKomisi->num_rows > 0) {
    while ($row = $resKomisi->fetch_assoc()) {
        $komisiList[] = $row;
    }
}

function buildKomisiPageUrl($pageNum, $selectedTglMulai = '', $selectedTglSelesai = '', $selectedBulan = 0, $selectedTahun = 0, $search = '') {
    $params = ['page' => $pageNum];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($selectedTglMulai)) $params['tgl_mulai'] = $selectedTglMulai;
    if (!empty($selectedTglSelesai)) $params['tgl_selesai'] = $selectedTglSelesai;
    if ($selectedBulan > 0) $params['bulan'] = $selectedBulan;
    if ($selectedTahun > 0) $params['tahun'] = $selectedTahun;
    return SystemInfo::app('CLIENT_URL') . '/komisi?' . http_build_query($params);
}

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<div class="main-content-inner py-3 py-md-4">
    <!-- 1. Header Banner Card (Maroon Gradient Style) -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-12">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-crown text-warning me-1"></i> Master Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Komisi Master</h2>
                            <p class="text-white-50 small mb-0">Rekapitulasi komisi & apresiasi dari Admin atas kontribusi kemitraan investor Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Metrics Summary Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Card 1: Total Komisi Diterima -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-solid fa-trophy fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Total Komisi Diterima</div>
                        <div class="fs-4 fw-bold text-success mb-0">Rp <?= number_format($stats['total_komisi'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Komisi Bulan Ini -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Komisi Bulan Ini</div>
                        <div class="fs-4 fw-bold text-primary mb-0">Rp <?= number_format($stats['komisi_bulan_ini'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Table Card Data Komisi (With Live Search & Filter Button) -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-body-emphasis mb-1 fs-6">
                            <i class="fa-solid fa-award me-2 text-danger"></i>Riwayat Penyerahan Komisi
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
                        <p class="text-body-secondary small mb-0">Pantau seluruh riwayat bukti transfer & komisi dari Admin</p>
                    </div>

                    <!-- Live Search & Tombol Filter Utama -->
                    <div class="d-flex align-items-center gap-2 flex-nowrap ms-auto">
                        <!-- Live Search Input Box -->
                        <div class="input-group input-group-sm" style="width: 180px; sm:width: 220px;">
                            <span class="input-group-text bg-body border-danger-subtle rounded-start-pill text-body-secondary"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input type="text" id="liveSearchKomisi" class="form-control border-danger-subtle rounded-end-pill fw-semibold text-body bg-body shadow-sm" value="<?= htmlspecialchars($search); ?>" placeholder="Cari komisi..." title="Live Search Komisi">
                        </div>

                        <!-- Tombol Filter Utama (Membuka Modal Filter Data) -->
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterKomisi">
                            <i class="fa-solid fa-filter me-1"></i> Filter Data
                        </button>
                    </div>
                </div>

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table id="table-client-komisi" class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3 text-center" style="width: 50px;">No</th>
                                    <th class="text-center">Tanggal Transfer</th>
                                    <th>Periode / Keterangan</th>
                                    <th class="text-center">Nominal Komisi</th>
                                    <th class="text-center">Bukti Bayar</th>
                                    <th class="text-start pe-3">Catatan Admin</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (!empty($komisiList)) : ?>
                                    <?php $no = $offset + 1; foreach ($komisiList as $km) : ?>
                                        <tr class="komisi-data-row">
                                            <td class="ps-3 text-center fw-bold text-body-secondary"><?= $no++ ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-body-tertiary border text-body-emphasis px-2.5 py-1 rounded-3 fw-semibold font-monospace small">
                                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                    <?= date("d/m/Y H:i", strtotime($km['tanggal_komisi'])) ?> WIB
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($km['periode']) ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold fs-12">
                                                    + Rp <?= number_format($km['nominal'], 0, ',', '.') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($km['bukti_pembayaran'])) : ?>
                                                    <?php 
                                                    $fileExt = strtolower(pathinfo($km['bukti_pembayaran'], PATHINFO_EXTENSION)); 
                                                    $proxyUrl = SystemInfo::app('CLIENT_URL') . '/image-proxy.php?file=' . urlencode($km['bukti_pembayaran']);
                                                    ?>
                                                    <?php if ($fileExt === 'pdf') : ?>
                                                        <a href="<?= $proxyUrl ?>" target="_blank" class="btn btn-outline-info btn-xs rounded-pill px-2.5 py-1 shadow-xs fw-bold" style="font-size: 11px;">
                                                            <i class="fa-solid fa-file-pdf me-1"></i> Lihat PDF
                                                        </a>
                                                    <?php else : ?>
                                                        <button type="button" class="btn btn-outline-info btn-xs btn-client-view-bukti-komisi rounded-pill px-2.5 py-1 shadow-xs fw-bold" style="font-size: 11px;"
                                                                data-img="<?= $proxyUrl ?>" 
                                                                data-master="<?= htmlspecialchars($user['MBR_NAME'] ?? 'Master Owner') ?>"
                                                                data-periode="<?= htmlspecialchars($km['periode']) ?>" 
                                                                data-nominal="Rp <?= number_format($km['nominal'], 0, ',', '.') ?>">
                                                            <i class="fa-solid fa-image me-1"></i> Lihat Bukti
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <span class="badge bg-light text-body-secondary border" style="font-size: 11px;">Belum ada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-start pe-3 small text-body-secondary">
                                                <?= htmlspecialchars($km['catatan'] ?: '-') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-body-secondary">
                                            <i class="fa-solid fa-receipt fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                            Belum ada riwayat penerimaan komisi master terdaftar yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls & Record Summary Footer -->
                    <?php if ($totalRecords > 0) : ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-body-subtle mt-2">
                            <div class="small text-body-secondary fw-semibold ms-1">
                                Menampilkan <span class="text-body-emphasis fw-bold"><?= ($totalRecords > 0) ? ($offset + 1) : 0; ?></span> - <span class="text-body-emphasis fw-bold"><?= min($offset + $limit, $totalRecords); ?></span> dari <span class="text-body-emphasis fw-bold"><?= $totalRecords; ?></span> komisi terdaftar
                            </div>

                            <?php if ($totalPages > 1) : ?>
                                <nav aria-label="Navigasi Halaman Komisi">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-start-pill text-body-emphasis px-3" href="<?= buildKomisiPageUrl($page - 1, $selectedTglMulai, $selectedTglSelesai, $selectedBulan, $selectedTahun, $search); ?>">
                                                <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                            </a>
                                        </li>

                                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                            <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>">
                                                <a class="page-link <?= ($i === $page) ? 'bg-danger border-danger text-white fw-bold' : 'text-body-emphasis'; ?> px-3" href="<?= buildKomisiPageUrl($i, $selectedTglMulai, $selectedTglSelesai, $selectedBulan, $selectedTahun, $search); ?>"><?= $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-end-pill text-body-emphasis px-3" href="<?= buildKomisiPageUrl($page + 1, $selectedTglMulai, $selectedTglSelesai, $selectedBulan, $selectedTahun, $search); ?>">
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

<!-- MODAL: FILTER DATA KOMISI MASTER -->
<div class="modal fade" id="modalFilterKomisi" tabindex="-1" aria-labelledby="modalFilterKomisiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg bg-body" style="border-radius: 20px;">
            <div class="modal-header border-bottom border-body-subtle py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="modal-title fw-extrabold text-body-emphasis mb-0 fs-6" id="modalFilterKomisiLabel">
                        <i class="fa-solid fa-filter me-2 text-danger"></i>Filter Data Komisi Master
                    </h6>
                    <small class="text-body-secondary" style="font-size: 11px;">Pilih rentang tanggal komisi</small>
                </div>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="<?= SystemInfo::app('CLIENT_URL'); ?>/komisi">
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-body-secondary mb-0">
                            <i class="fa-regular fa-calendar-range me-1 text-danger"></i>Pilih Rentang Tanggal Transfer
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
                    <a href="<?= SystemInfo::app('CLIENT_URL'); ?>/komisi" class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 12px;">
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

<script>
$(document).ready(function() {

    // Instant Keyup Live Search for Komisi Table
    $('#liveSearchKomisi').on('keyup search', function() {
        let val = $(this).val().toLowerCase().trim();
        $('.komisi-data-row').each(function() {
            let text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) > -1);
        });
    });

    $(document).on('click', '.btn-client-view-bukti-komisi', function() {
        let imgUrl  = $(this).data('img');
        let master  = $(this).data('master');
        let periode = $(this).data('periode');
        let nominal = $(this).data('nominal');

        var infoHtml = '<div class="text-start bg-light p-3 rounded mb-3" style="font-size:13.5px; border:1px solid #e9ecef;">'
            + '<div class="d-flex align-items-center mb-2">'
            + '  <i class="fa-solid fa-user-circle text-primary me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Master Owner:</span>'
            + '  <span class="text-dark fw-semibold">' + (master || '-') + '</span>'
            + '</div>'
            + '<div class="d-flex align-items-center mb-2">'
            + '  <i class="fa-solid fa-calendar-check text-success me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Keterangan:</span>'
            + '  <span class="text-dark">' + (periode || '-') + '</span>'
            + '</div>'
            + '<div class="d-flex align-items-center">'
            + '  <i class="fa-solid fa-money-bill-wave text-warning me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Nominal Komisi:</span>'
            + '  <span class="text-success fw-bold">' + nominal + '</span>'
            + '</div>'
            + '</div>';

        Swal.fire({
            title: '<div class="fw-bold text-danger fs-5"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Bukti Pembayaran Komisi Master</div>',
            html: infoHtml
                + '<img src="' + imgUrl + '" '
                + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
                + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\';><i class=\\\'fa-solid fa-triangle-exclamation me-1\\\'></i> Gambar gagal dimuat</p>\'">',
            showCloseButton: true,
            showConfirmButton: false,
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });
});
</script>
