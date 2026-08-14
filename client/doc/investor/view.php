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
    $whereConds[] = "(u.nama_lengkap LIKE '%{$safeSearch}%' OR u.username LIKE '%{$safeSearch}%' OR u.no_hp LIKE '%{$safeSearch}%' OR u.kecamatan LIKE '%{$safeSearch}%' OR u.alamat_lengkap LIKE '%{$safeSearch}%')";
}

if (!empty($selectedTglMulai) && !empty($selectedTglSelesai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConds[] = "DATE(u.created_at) BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
} elseif (!empty($selectedTglMulai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $whereConds[] = "DATE(u.created_at) >= '{$safeMulai}'";
} elseif (!empty($selectedTglSelesai)) {
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereConds[] = "DATE(u.created_at) <= '{$safeSelesai}'";
} else {
    if ($selectedBulan > 0) {
        $whereConds[] = "MONTH(u.created_at) = {$selectedBulan}";
    }
    if ($selectedTahun > 0) {
        $whereConds[] = "YEAR(u.created_at) = {$selectedTahun}";
    }
}

$whereSql = "WHERE " . implode(" AND ", $whereConds);

// Pagination setup
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Count Total Investors for Master matching filter
$sqlCount = "
    SELECT COUNT(DISTINCT i.id_investor) as total 
    FROM investor i 
    JOIN users u ON u.id_users = i.id_users 
    {$whereSql}
";
$resTotalInv = $db->query($sqlCount);
$totalRecords = ($resTotalInv && $rowT = $resTotalInv->fetch_assoc()) ? (int)$rowT['total'] : 0;
$totalPages   = ($totalRecords > 0) ? (int)ceil($totalRecords / $limit) : 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Count Total Overall Active Outlets for Metric Card
$resTotalActiveOut = $db->query("
    SELECT COUNT(o.id_outlet) as total 
    FROM outlet o
    JOIN investor i ON i.id_investor = o.id_investor
    WHERE (i.id_master = {$userId} OR i.id_master IS NULL)
      AND o.status = 'active'
      AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW())
");
$sumOutlets = ($resTotalActiveOut && $rowAO = $resTotalActiveOut->fetch_assoc()) ? (int)$rowAO['total'] : 0;

// Fetch distinct years of investor registration
$availableYears = [];
$resYears = $db->query("SELECT DISTINCT YEAR(u.created_at) as y_periode FROM investor i JOIN users u ON u.id_users = i.id_users WHERE (i.id_master = {$userId} OR i.id_master IS NULL) ORDER BY y_periode DESC");
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

// Fetch Paginated Investors List
$sqlInv = "
    SELECT 
        i.id_investor,
        u.nama_lengkap,
        u.username,
        u.no_hp,
        u.kecamatan,
        u.alamat_lengkap as alamat_investor,
        u.created_at as tanggal_bergabung,
        COUNT(o.id_outlet) as total_outlet,
        SUM(CASE WHEN o.status = 'active' AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW()) THEN 1 ELSE 0 END) as total_aktif
    FROM investor i
    JOIN users u ON u.id_users = i.id_users
    LEFT JOIN outlet o ON o.id_investor = i.id_investor
    {$whereSql}
    GROUP BY i.id_investor
    ORDER BY i.id_investor DESC
    LIMIT {$limit} OFFSET {$offset}
";

$resInvestors = $db->query($sqlInv);
$investorList = [];
if ($resInvestors && $resInvestors->num_rows > 0) {
    while ($row = $resInvestors->fetch_assoc()) {
        $invId = (int)$row['id_investor'];
        $outlets = [];
        $sqlOut = "SELECT o.nama_outlet, u.kecamatan, u.alamat_lengkap as alamat_outlet, 
                          o.tgl_disetujui as tanggal_bergabung 
                   FROM outlet o 
                   JOIN users u ON u.id_users = o.id_users 
                   WHERE o.id_investor = {$invId} AND o.status = 'active' AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW())
                   ORDER BY o.id_outlet DESC";
        $resOut = $db->query($sqlOut);
        if ($resOut) {
            while ($out = $resOut->fetch_assoc()) {
                $outlets[] = $out;
            }
        }
        $row['outlets_data'] = $outlets;
        $investorList[] = $row;
    }
}

function buildInvestorPageUrl($pageNum, $selectedTglMulai = '', $selectedTglSelesai = '', $search = '') {
    $params = ['page' => $pageNum];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($selectedTglMulai)) $params['tgl_mulai'] = $selectedTglMulai;
    if (!empty($selectedTglSelesai)) $params['tgl_selesai'] = $selectedTglSelesai;
    return SystemInfo::app('CLIENT_URL') . '/investor?' . http_build_query($params);
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
                                <i class="fa-solid  text-warning me-1"></i> Master Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Data Investor</h2>
                            <p class="text-white-50 small mb-0">Memantau daftar seluruh investor dan toko yang berada di bawah naungan Master Owner.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Card -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);">
                        <i class="fa-solid fa-user-tie fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Total Investor Saya</div>
                        <div class="fs-4 fw-extrabold text-danger mb-0"><?= number_format($totalRecords, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Investor</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-solid fa-store fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Total Outlet Aktif</div>
                        <div class="fs-4 fw-extrabold text-success mb-0"><?= number_format($sumOutlets, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Outlet</span></div>
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
                            <i class="fa-solid fa-users me-2 text-danger"></i>Daftar Investor Terdaftar
                            <?php if ($selectedBulan > 0 || $selectedTahun > 0) : ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2 fw-bold" style="font-size: 10px;">
                                    <i class="fa-solid fa-calendar-range me-1"></i>
                                    <?= $selectedBulan > 0 ? $bulanIndo[$selectedBulan] : 'Semua Bulan'; ?> <?= $selectedTahun > 0 ? $selectedTahun : ''; ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                        <p class="text-body-secondary small mb-0">Kelola dan pantau seluruh data investor di bawah naungan Master Owner</p>
                    </div>

                    <!-- Live Search & Tombol Filter Utama -->
                    <div class="d-flex align-items-center gap-2 flex-nowrap ms-auto">
                        <!-- Live Search Input Box -->
                        <div class="input-group input-group-sm" style="width: 180px; sm:width: 220px;">
                            <span class="input-group-text bg-body border-danger-subtle rounded-start-pill text-body-secondary"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input type="text" id="liveSearchInvestor" class="form-control border-danger-subtle rounded-end-pill fw-semibold text-body bg-body shadow-sm" value="<?= htmlspecialchars($search); ?>" placeholder="Cari investor..." title="Live Search Nama Investor">
                        </div>

                        <!-- Tombol Filter Utama -->
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1 text-nowrap" id="btnFilterInvestor">
                            <i class="fa-solid fa-filter me-1"></i> Filter Data
                        </button>
                    </div>
                </div>

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableDataInvestor">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3 text-center" style="width: 50px;">No</th>
                                    <th>Nama Investor</th>
                                    <th class="text-center">Kecamatan</th>
                                    <th class="text-center">Total Outlet Aktif</th>
                                    <th class="text-center pe-3">Tanggal Bergabung</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (!empty($investorList)) : ?>
                                    <?php foreach ($investorList as $index => $inv) : ?>
                                        <tr class="investor-data-row">
                                            <td class="ps-3 text-center fw-bold text-body-secondary"><?= $offset + $index + 1; ?></td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($inv['nama_lengkap']); ?></div>
                                                <div class="text-body-secondary small mt-0.5">
                                                    <span class="text-success"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($inv['no_hp'] ?? '-'); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                 <?php if (!empty($inv['alamat_investor'])) : ?>
                                                     <span class="badge bg-light text-body-secondary border btn-detail-alamat-investor shadow-xs" style="font-size: 11px; cursor: pointer;"
                                                           data-nama="<?= htmlspecialchars($inv['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-kecamatan="<?= htmlspecialchars($inv['kecamatan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-alamat = "<?= htmlspecialchars($inv['alamat_investor'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                           title="Klik untuk lihat detail alamat">
                                                         <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($inv['kecamatan'] ?: 'N/A'); ?>
                                                     </span>
                                                 <?php else : ?>
                                                     <span class="badge bg-light text-body-secondary border" style="font-size: 11px;">
                                                         <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($inv['kecamatan'] ?: 'N/A'); ?>
                                                     </span>
                                                 <?php endif; ?>
                                             </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold fs-12 btn-lihat-outlet shadow-sm" style="cursor: pointer;" data-nama="<?= htmlspecialchars($inv['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?>" data-outlets="<?= htmlspecialchars(json_encode($inv['outlets_data'] ?? []), ENT_QUOTES, 'UTF-8'); ?>" title="Klik untuk melihat detail outlet">
                                                    <i class="fa-solid fa-store me-1"></i><?= number_format($inv['total_aktif']); ?> Outlet
                                                </span>
                                            </td>
                                            <td class="text-center pe-3">
                                                <span class="badge bg-body-tertiary border text-body-emphasis px-2.5 py-1 rounded-3 fw-semibold font-monospace small">
                                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                    <?= !empty($inv['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($inv['tanggal_bergabung'])) . ' WIB' : '-'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-body-secondary">
                                            <i class="fa-solid fa-users-slash fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                            Belum ada data investor terdaftar yang sesuai dengan filter.
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
                                Menampilkan <span class="text-body-emphasis fw-bold"><?= ($totalRecords > 0) ? ($offset + 1) : 0; ?></span> - <span class="text-body-emphasis fw-bold"><?= min($offset + $limit, $totalRecords); ?></span> dari <span class="text-body-emphasis fw-bold"><?= $totalRecords; ?></span> investor terdaftar
                            </div>

                            <?php if ($totalPages > 1) : ?>
                                <nav aria-label="Navigasi Halaman Investor">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-start-pill text-body-emphasis px-3" href="<?= buildInvestorPageUrl($page - 1, $selectedTglMulai, $selectedTglSelesai, $search); ?>">
                                                <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                            </a>
                                        </li>

                                        <?php for ($p = 1; $p <= $totalPages; $p++) : ?>
                                            <li class="page-item <?= ($p === $page) ? 'active' : ''; ?>">
                                                <a class="page-link <?= ($p === $page) ? 'bg-danger border-danger text-white fw-bold' : 'text-body-emphasis'; ?>" href="<?= buildInvestorPageUrl($p, $selectedTglMulai, $selectedTglSelesai, $search); ?>"><?= $p; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-end-pill text-body-emphasis px-3" href="<?= buildInvestorPageUrl($page + 1, $selectedTglMulai, $selectedTglSelesai, $search); ?>">
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





<script type="text/javascript">
$(document).ready(function() {

    // Instant Keyup Filter for Investor Table
    $('#liveSearchInvestor').on('keyup search', function() {
        let val = $(this).val().toLowerCase().trim();
        $('.investor-data-row').each(function() {
            let text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) > -1);
        });
    });

    $('#btnFilterInvestor').on('click', function() {
        let currentBulan = "<?= $selectedBulan; ?>";
        let currentTahun = "<?= $selectedTahun; ?>";
        let baseUrl = "<?= SystemInfo::app('CLIENT_URL'); ?>/investor";
        
        let html = `
            <form id="formFilterSwal" method="GET" action="${baseUrl}" class="text-start fs-14 mt-4 px-1">
                <div class="row g-3 m-0">
                    <div class="col-6 ps-0">
                        <label class="text-body-secondary small fw-bold d-block mb-1">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                            <option value="0" ${currentBulan == '0' ? 'selected' : ''}>Semua Bulan</option>
                            <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                <option value="<?= $mNum; ?>" ${currentBulan == '<?= $mNum; ?>' ? 'selected' : ''}><?= $mName; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 pe-0">
                        <label class="text-body-secondary small fw-bold d-block mb-1">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                            <option value="0" ${currentTahun == '0' ? 'selected' : ''}>Semua Tahun</option>
                            <?php foreach ($availableYears as $y) : ?>
                                <option value="<?= $y; ?>" ${currentTahun == '<?= $y; ?>' ? 'selected' : ''}><?= $y; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-body-subtle">
                    <a href="${baseUrl}" class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 13px;">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3 py-1.5 fw-semibold swal-cancel-btn" style="font-size: 13px;">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 py-1.5 fw-bold shadow-sm" style="background-color: #7D0A0A; border-color: #7D0A0A; font-size: 13px;">
                            <i class="fa-solid fa-filter me-1"></i> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        `;

        Swal.fire({
            title: '<div class="fw-bold text-danger fs-5 text-start"><i class="fa-solid fa-filter me-2"></i>Filter Data Investor</div><div class="text-start text-body-secondary mt-1" style="font-size: 12px; font-weight: normal;">Pilih periode pendaftaran investor berdasarkan bulan & tahun</div>',
            html: html,
            showConfirmButton: false,
            customClass: { popup: 'rounded-4 p-4' },
            didOpen: () => {
                $('.swal-cancel-btn').on('click', () => Swal.close());
            }
        });
    });

    $(document).on('click', '.btn-detail-alamat-investor', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');
        let queryStr = encodeURIComponent((nama ? nama + ' ' : '') + (kec && kec !== '-' ? 'Kec. ' + kec + ' ' : '') + (alamat && alamat !== '-' ? alamat : ''));
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;

        let html = `
            <div class="text-start fs-14">
                <div class="p-3 bg-light rounded-4 border mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-user-tie text-danger me-2"></i>Nama Investor</span>
                        <span class="fw-bold text-dark">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Kecamatan</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">${kec}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-body-secondary d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-2"></i>Alamat Lengkap:</span>
                        <div class="p-3 bg-white rounded-3 border text-dark fw-semibold" style="font-size: 13.5px; line-height: 1.6; word-break: break-word;">
                            <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-underline fw-bold d-block mb-1" title="Klik untuk membuka lokasi di Google Maps">
                                ${alamat} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-primary" style="font-size: 11px;"></i>
                            </a>
                            <small class="text-muted d-block text-start mt-2 pt-2 border-top" style="font-size: 11px; font-weight: normal;">
                                <i class="fa-solid fa-circle-info me-1 text-danger"></i>Klik teks alamat di atas untuk membuka lokasi di Google Maps (Desktop / Aplikasi HP)
                            </small>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-danger rounded-pill px-3 py-2 fw-bold w-100 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-map-location-dot fs-6"></i> Buka Petunjuk Arah di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            title: '<div class="fw-bold text-danger fs-5"><i class="fa-solid fa-building-user me-2"></i>Detail Lokasi Investor</div>',
            html: html,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#6c757d',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });

    $(document).on('click', '.btn-lihat-outlet', function() {
        let namaInv = $(this).data('nama');
        let outlets = $(this).data('outlets');

        let html = '<div class="table-responsive"><table class="table table-hover align-middle mb-0 w-100 text-start" style="font-size: 13.5px;">';
        html += '<thead class="table-group-divider bg-body-secondary text-uppercase small text-body-secondary"><tr><th class="ps-3 text-center" style="width: 50px;">No</th><th>Nama Outlet</th><th class="text-center">Kecamatan</th><th class="text-center">Tanggal Bergabung</th></tr></thead>';
        html += '<tbody class="border-0">';

        if (outlets && outlets.length > 0) {
            $.each(outlets, function(idx, item) {
                let kecText = item.kecamatan ? item.kecamatan : '-';
                let safeNama = String(item.nama_outlet).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                let safeKec = String(kecText).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                let safeAlamat = String(item.alamat_outlet || '-').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                let locColHtml = item.alamat_outlet ? 
                    `<span class="badge bg-light text-body-secondary border btn-detail-alamat-outlet-item shadow-xs" style="font-size: 11px; cursor: pointer;" onclick="$(this).closest('tr').next('.detail-lokasi-row').fadeToggle(200);" title="Klik untuk lihat/tutup detail alamat"><i class="fa-solid fa-location-dot me-1 text-danger"></i>${kecText} <i class="fa-solid fa-caret-down ms-1"></i></span>` :
                    `<span class="badge bg-light text-body-secondary border" style="font-size: 11px;"><i class="fa-solid fa-location-dot me-1 text-danger"></i>${kecText}</span>`;
                let tglJoin = item.tanggal_bergabung ? item.tanggal_bergabung : (item.tgl_disetujui ? item.tgl_disetujui : '-');
                let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent((safeNama ? safeNama + ' ' : '') + (safeKec && safeKec !== '-' ? 'Kec. ' + safeKec + ' ' : '') + safeAlamat);

                html += `
                    <tr>
                        <td class="ps-3 text-center fw-bold text-muted">${idx + 1}</td>
                        <td><strong class="text-body-emphasis fs-6">${safeNama}</strong></td>
                        <td class="text-center">${locColHtml}</td>
                        <td class="text-center small text-body-secondary">${tglJoin}</td>
                    </tr>
                `;
                
                if (item.alamat_outlet) {
                    html += `
                    <tr class="detail-lokasi-row" style="display: none;">
                        <td class="border-0"></td>
                        <td colspan="3" class="py-2 pe-3 border-0">
                            <div class="p-3 bg-white border border-danger-subtle rounded-3 shadow-sm d-flex align-items-start gap-3 text-start w-100" style="word-break: break-word;">
                                <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-map-location-dot fs-6"></i>
                                </div>
                                <div class="flex-grow-1 text-start">
                                    <span class="d-block text-body-secondary small fw-bold mb-1">Alamat Lengkap Outlet:</span>
                                    <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-underline fw-bold d-inline-block mb-1" style="font-size: 13px;" title="Klik untuk membuka lokasi di Google Maps">
                                        ${safeAlamat} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-primary" style="font-size: 10px;"></i>
                                    </a>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        <i class="fa-solid fa-circle-info text-danger me-1"></i>Klik teks alamat untuk petunjuk arah di Google Maps (Desktop / Aplikasi HP)
                                    </small>
                                </div>
                            </div>
                        </td>
                    </tr>
                    `;
                }
            });
        } else {
            html += '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fa-solid fa-store-slash me-2 opacity-50"></i>Investor ini belum memiliki toko aktif.</td></tr>';
        }
        
        html += '</tbody></table></div>';

        Swal.fire({
            title: `<div class="fw-bold text-danger fs-5 text-start"><i class="fa-solid fa-store me-2"></i>Total Outlet Investor: <span class="fw-bold text-danger">${namaInv}</span></div>`,
            html: html,
            width: '800px',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });


});
</script>
