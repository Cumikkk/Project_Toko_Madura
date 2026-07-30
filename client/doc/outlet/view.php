<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);
$role = strtolower($user['role'] ?? '');

if ($role === 'master') {
    // -------------------------------------------------------------
    // CLIENT OUTLET VIEW UNTUK MASTER (LIST OUTLET MONITORING)
    // -------------------------------------------------------------
    $listMasterOutlets = $db->query("
        SELECT o.id_outlet, o.nama_outlet, o.kecamatan as kecamatan_outlet, o.alamat_outlet, o.tanggal_bergabung,
               u_inv.nama_lengkap as nama_investor, i.kecamatan as kecamatan_investor, i.alamat_investor, u_out.username as username_outlet
        FROM outlet o
        JOIN investor i ON i.id_investor = o.id_investor
        JOIN users u_inv ON u_inv.id_users = i.id_users
        LEFT JOIN users u_out ON u_out.id_users = o.id_users
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
        ORDER BY o.id_outlet DESC
    ");
    $totalOutletMaster = $listMasterOutlets ? $listMasterOutlets->num_rows : 0;
?>

<div class="main-content-inner py-3 py-md-4">
    <!-- Header Banner Card -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-12">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-user-crown me-1"></i> Master Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Data Outlet Sub-Investor</h2>
                            <p class="text-white-50 small mb-0">Memantau daftar seluruh outlet yang berada di bawah kepemilikan Investor naungan Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Card -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px; border-left: 5px solid #198754 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-light fa-store fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary small fw-semibold">Total Outlet Terikat</div>
                        <div class="fs-4 fw-bold text-success mb-0"><?= number_format($totalOutletMaster, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Outlet</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-body py-3 px-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle">
                    <h5 class="fw-bold text-body-emphasis mb-0 fs-6"><i class="fa-solid fa-store me-2 text-success"></i>Daftar Seluruh Outlet</h5>
                </div>

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>Nama Outlet & Alamat</th>
                                    <th>Investor Pemilik & Alamat</th>
                                    <th>Tanggal Bergabung</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if ($listMasterOutlets && $listMasterOutlets->num_rows > 0) : ?>
                                    <?php $no = 1; while ($row = $listMasterOutlets->fetch_assoc()) : ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-body-secondary"><?= $no++; ?></td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($row['nama_outlet']); ?></div>
                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                    <span class="badge bg-light text-body-secondary border" style="font-size: 10px;"><i class="fa-light fa-location-dot me-1 text-success"></i><?= htmlspecialchars($row['kecamatan_outlet'] ?: 'Kecamatan N/A') ?></span>
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-detail-alamat-outlet rounded-pill px-2 py-0" style="font-size: 10px;"
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>"
                                                            data-kecamatan="<?= htmlspecialchars($row['kecamatan_outlet'] ?: '-') ?>"
                                                            data-alamat="<?= htmlspecialchars($row['alamat_outlet'] ?: '-') ?>">
                                                        Detail Alamat
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-body-emphasis"><?= htmlspecialchars($row['nama_investor']) ?></span>
                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                    <span class="badge bg-light text-body-secondary border" style="font-size: 10px;"><i class="fa-light fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($row['kecamatan_investor'] ?: 'Kecamatan N/A') ?></span>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-detail-alamat-investor rounded-pill px-2 py-0" style="font-size: 10px;"
                                                            data-nama="<?= htmlspecialchars($row['nama_investor']) ?>"
                                                            data-kecamatan="<?= htmlspecialchars($row['kecamatan_investor'] ?: '-') ?>"
                                                            data-alamat="<?= htmlspecialchars($row['alamat_investor'] ?: '-') ?>">
                                                        Detail Alamat
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="small text-body-secondary">
                                                <?= !empty($row['tanggal_bergabung']) ? date('d M Y', strtotime($row['tanggal_bergabung'])) : '-' ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                    <i class="fa-solid fa-circle me-1" style="font-size: 7px;"></i>Aktif
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-body-secondary">Belum ada outlet terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('click', '.btn-detail-alamat-investor', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');
        Swal.fire({
            title: 'Detail Alamat Investor',
            html: `<div class="text-start fs-14"><div class="bg-body-tertiary p-3 rounded-3 border mb-3"><div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom"><span class="text-body-secondary">Investor:</span><span class="fw-bold">${nama}</span></div><div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom"><span class="text-body-secondary">Kecamatan:</span><span class="badge bg-primary-subtle text-primary rounded-pill px-3">${kec}</span></div><div><span class="text-body-secondary d-block mb-1">Alamat Lengkap:</span><p class="fw-semibold mb-0 bg-body p-2 rounded border">${alamat}</p></div></div></div>`,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A'
        });
    });

    $(document).on('click', '.btn-detail-alamat-outlet', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');
        Swal.fire({
            title: 'Detail Alamat Outlet',
            html: `<div class="text-start fs-14"><div class="bg-body-tertiary p-3 rounded-3 border mb-3"><div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom"><span class="text-body-secondary">Outlet:</span><span class="fw-bold">${nama}</span></div><div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom"><span class="text-body-secondary">Kecamatan:</span><span class="badge bg-success-subtle text-success rounded-pill px-3">${kec}</span></div><div><span class="text-body-secondary d-block mb-1">Alamat Lengkap:</span><p class="fw-semibold mb-0 bg-body p-2 rounded border">${alamat}</p></div></div></div>`,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#198754'
        });
    });
});
</script>
<?php
    return;
}

// Get Investor ID for logged-in user
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Get Investor ID for logged-in user
$resInv = $db->query("SELECT id_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
$investorId = 0;
if ($resInv && $resInv->num_rows > 0) {
    $investorId = (int)$resInv->fetch_assoc()['id_investor'];
} else {
    $db->query("INSERT INTO investor (id_users, id_master, alamat_investor, persen_bagian_investor) VALUES ({$userId}, 1, 'Bangkalan', 50.00)");
    $investorId = $db->insert_id;
}

// Filter Data Outlet Toko (Tanggal, Bulan, Tahun Dibuat)
$selectedTgl   = isset($_GET['tgl']) && !empty($_GET['tgl']) ? trim($_GET['tgl']) : '';
$selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$availableYears = [];
// Fetch distinct years of outlet registration for this investor
$resYears = $db->query("SELECT DISTINCT YEAR(tanggal_bergabung) as y_year FROM outlet WHERE id_investor = {$investorId} AND tanggal_bergabung IS NOT NULL ORDER BY y_year DESC");
if ($resYears) {
    while ($yRow = $resYears->fetch_assoc()) {
        if (!empty($yRow['y_year'])) {
            $availableYears[] = (int)$yRow['y_year'];
        }
    }
}
if (!in_array((int)date('Y'), $availableYears)) {
    array_unshift($availableYears, (int)date('Y'));
}

// Fetch system settings (fee & bank details) from pengaturan_sistem
$sysSettings = [];
$resSysSetting = $db->query("SELECT nama_pengaturan, nilai FROM pengaturan_sistem");
if ($resSysSetting) {
    while ($r = $resSysSetting->fetch_assoc()) {
        $sysSettings[$r['nama_pengaturan']] = $r['nilai'];
    }
}
$biayaLangganan = (float)($sysSettings['biaya_langganan_outlet'] ?? 100000.00);
$bankNama       = $sysSettings['bank_nama'] ?? 'BCA';
$bankNoRek      = $sysSettings['bank_no_rekening'] ?? '123-456-7890';
$bankAtasNama   = $sysSettings['bank_atas_nama'] ?? 'Toko Madura Pusat';

// Build WHERE clause for Outlet Registration Date & Ownership
$whereOutletConds = ["o.id_investor = {$investorId}"];

if (!empty($selectedTgl)) {
    $safeTgl = $db->real_escape_string($selectedTgl);
    $whereOutletConds[] = "DATE(o.tanggal_bergabung) = '{$safeTgl}'";
} else {
    if ($selectedBulan > 0) {
        $whereOutletConds[] = "MONTH(o.tanggal_bergabung) = {$selectedBulan}";
    }
    if ($selectedTahun > 0) {
        $whereOutletConds[] = "YEAR(o.tanggal_bergabung) = {$selectedTahun}";
    }
}
$whereOutletSql = "WHERE " . implode(" AND ", $whereOutletConds);

// Pagination Setup (Max 10 records per page)
$limit = 10;
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;

// Count Total Outlets Matching Filter
$sqlCount = "
    SELECT COUNT(*) as total
    FROM outlet o
    JOIN users u ON o.id_users = u.id_users
    {$whereOutletSql}
";
$resCount = $db->query($sqlCount);
$totalRecords = $resCount ? (int)$resCount->fetch_assoc()['total'] : 0;
$totalPages = ($totalRecords > 0) ? (int)ceil($totalRecords / $limit) : 1;

if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $limit;

// Fetch Outlets belonging to this Investor with Limit & Offset
$sqlOutlets = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        o.kecamatan,
        o.alamat_outlet,
        o.status,
        o.nominal_biaya,
        o.bukti_pembayaran,
        o.alasan_penolakan,
        o.tanggal_bergabung,
        o.id_users,
        u.username
    FROM outlet o
    JOIN users u ON o.id_users = u.id_users
    {$whereOutletSql}
    ORDER BY o.id_outlet DESC
    LIMIT {$limit} OFFSET {$offset}
";
$resOutlets = $db->query($sqlOutlets);
$outlets = [];

if ($resOutlets) {
    while ($row = $resOutlets->fetch_assoc()) {
        $outlets[] = $row;
    }
}
$totalOutlet = $totalRecords;

$periodeLabelStr = (!empty($selectedTgl) ? date('d/m/Y', strtotime($selectedTgl)) . ' ' : '') . 
    ($selectedBulan > 0 ? ($bulanIndo[$selectedBulan] ?? '') . ' ' : '') . 
    ($selectedTahun > 0 ? $selectedTahun : '');

if (empty($selectedTgl) && $selectedBulan === 0 && $selectedTahun === 0) {
    $periodeLabelStr = 'Semua Tanggal';
}

function buildOutletPageUrl($pageNum, $selectedTgl, $selectedBulan, $selectedTahun) {
    $params = ['page' => $pageNum];
    if (!empty($selectedTgl)) $params['tgl'] = $selectedTgl;
    if ($selectedBulan > 0) $params['bulan'] = $selectedBulan;
    if ($selectedTahun > 0) $params['tahun'] = $selectedTahun;
    return SystemInfo::app('CLIENT_URL') . '/outlet?' . http_build_query($params);
}
?>

<style>
/* Custom Pill Filter Bar for Outlet View */
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

.stat-card-icon-box {
    width: 48px;
    height: 48px;
}
.micro-text-responsive {
    font-size: 12px;
    letter-spacing: 0.5px;
}

@media (max-width: 575.98px) {
    .filter-pill-container {
        width: 100%;
        justify-content: space-between;
    }
    .stat-card-icon-box {
        width: 36px !important;
        height: 36px !important;
    }
    .micro-text-responsive {
        font-size: 10px !important;
        letter-spacing: 0.3px;
        line-height: 1.1;
@media (min-width: 992px) {
    .border-end-lg {
        border-right: 1px solid var(--bs-border-color, #dee2e6) !important;
    }
}

/* Precise Vertical Alignment for Wizard Modal Labels & Input Placeholders */
#modalTambahOutlet .form-label.required::after,
#modalTambahOutlet label.required::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
}
#modalTambahOutlet .form-label {
    margin-top: 18px !important;
    margin-bottom: 5px !important;
    display: block !important;
    font-weight: 700 !important;
    font-size: 11.5px !important;
    line-height: 1.4 !important;
    overflow: visible !important;
    white-space: normal !important;
    color: var(--bs-body-color);
}
#modalTambahOutlet #stepSection1 > .row > div:first-child .form-label:first-child,
#modalTambahOutlet #stepSection2 > .row > div:first-child .form-label:first-child {
    margin-top: 4px !important;
}
#modalTambahOutlet .form-control,
#modalTambahOutlet .input-group-text {
    font-size: 12px !important;
    padding: 7px 12px !important;
    height: 38px !important;
    line-height: 1.5 !important;
    border-radius: 8px !important;
}
#modalTambahOutlet .input-group .form-control.rounded-start-3 {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}
#modalTambahOutlet .input-group .input-group-text {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}
</style>

<div class="main-content-inner py-3 py-md-4">
    <!-- Header Banner Card -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8 col-md-7">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-user-shield me-1"></i> Investor Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Data Outlet Sub-Investor</h2>
                            <p class="text-white-50 small mb-0">Kelola daftar outlet di bawah kepemilikan Anda, daftarkan akun outlet baru, dan pantau rincian omzet bulanan.</p>
                        </div>
                        <div class="col-lg-4 col-md-5 text-md-end text-start">
                            <button type="button" class="btn btn-light text-danger fw-bold px-4 py-3 shadow rounded-pill w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#modalTambahOutlet">
                                <i class="fa-solid fa-plus me-2"></i> Tambah Outlet Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Card -->
    <div class="row mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs stat-card-icon-box" style="background: linear-gradient(135deg, #7D0A0A 0%, #580608 100%);">
                            <i class="fa-light fa-store fs-4"></i>
                        </div>
                        <div class="min-w-0 flex-fill">
                            <div class="text-body-secondary text-uppercase fw-bold micro-text-responsive mb-1 text-truncate">Total Outlet Milik Anda</div>
                            <div class="fs-4 fs-md-3 fw-extrabold text-body-emphasis mb-0">
                                <?= number_format($totalOutlet, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Outlet</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                <!-- Header with Title & Filter Trigger Button -->
                <div class="card-header bg-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-body-emphasis mb-1 fs-6"><i class="fa-solid fa-store me-2 text-danger"></i>Daftar Outlet Terdaftar</h5>
                        <p class="text-body-secondary small mb-0">Kelola dan pantau daftar akun outlet di bawah kepemilikan Anda</p>
                    </div>

                    <!-- Live Search & Tombol Filter Utama (Side-by-Side Flex Nowrap) -->
                    <div class="d-flex align-items-center gap-2 flex-nowrap ms-auto">
                        <!-- Live Search Input Box -->
                        <div class="input-group input-group-sm" style="width: 180px; sm:width: 220px;">
                            <span class="input-group-text bg-body border-danger-subtle rounded-start-pill text-body-secondary"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input type="text" id="liveSearchOutlet" class="form-control border-danger-subtle rounded-end-pill fw-semibold text-body bg-body shadow-sm" placeholder="Cari nama outlet..." title="Live Search Nama Outlet">
                        </div>

                        <!-- Tombol Filter Utama (Membuka Modal Filter Data) -->
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterOutlet">
                            <i class="fa-solid fa-filter me-1"></i> Filter Data
                        </button>
                    </div>
                </div>

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableDataOutlet">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>Nama Outlet</th>
                                    <th>Username Akun</th>
                                    <th>Waktu Pendaftaran</th>
                                    <th class="text-center">Alamat Outlet</th>
                                    <th>Status</th>
                                    <th class="text-center pe-3" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (!empty($outlets)) : ?>
                                    <?php foreach ($outlets as $index => $row) : ?>
                                        <tr class="outlet-data-row">
                                            <td class="ps-3 fw-bold text-body-secondary"><?= $offset + $index + 1; ?></td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6">
                                                    <i class="fa-solid fa-store text-danger me-1"></i><?= htmlspecialchars($row['nama_outlet']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-body-secondary fw-semibold">
                                                    <i class="fa-light fa-user me-1"></i>@<?= htmlspecialchars($row['username']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-body-tertiary border text-body-emphasis px-2 py-1 rounded-3 fw-semibold font-monospace small">
                                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                    <?= !empty($row['tanggal_bergabung']) ? date('d/m/Y H:i', strtotime($row['tanggal_bergabung'])) . ' WIB' : '-'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2.5 flex-nowrap">
                                                    <span class="badge bg-body-tertiary text-body-emphasis border px-2.5 py-1.5 rounded-2 fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($row['kecamatan'] ?: ($row['alamat_outlet'] ?: '-')); ?>
                                                    </span>
                                                    <?php if (!empty($row['alamat_outlet'])) : ?>
                                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 btn-detail-alamat" 
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet']); ?>"
                                                            data-kecamatan="<?= htmlspecialchars($row['kecamatan'] ?: '-'); ?>"
                                                            data-alamat="<?= htmlspecialchars($row['alamat_outlet']); ?>"
                                                            style="font-size: 10.5px; font-weight: 700;">
                                                            <i class="fa-solid fa-circle-info me-1"></i>Detail
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (($row['status'] ?? 'active') === 'pending') : ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill fw-semibold" title="Menunggu Konfirmasi Pembayaran Admin">
                                                        <i class="fa-regular fa-clock me-1"></i>Menunggu Verifikasi Admin
                                                    </span>
                                                <?php elseif (($row['status'] ?? 'active') === 'reject') : ?>
                                                    <div class="d-flex flex-column align-items-center justify-content-center gap-1.5 py-1">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold">
                                                            <i class="fa-solid fa-circle-xmark me-1"></i>Ditolak Admin
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2.5 rounded-pill btn-cek-alasan shadow-xs"
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-alasan="<?= htmlspecialchars($row['alasan_penolakan'] ?: 'Tidak ada catatan alasan dari admin.', ENT_QUOTES, 'UTF-8'); ?>"
                                                            style="font-size: 10.5px; font-weight: 700;">
                                                            <i class="fa-solid fa-circle-exclamation me-1"></i>Cek Alasan
                                                        </button>
                                                    </div>
                                                <?php else : ?>
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    $jt = !empty($row['tgl_jatuh_tempo']) ? date('Y-m-d', strtotime($row['tgl_jatuh_tempo'])) : null;
                                                    if ($jt && $today > $jt) :
                                                    ?>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold" title="Masa Langganan Telah Berakhir">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Expired (<?= date('d/m/Y', strtotime($jt)); ?>)
                                                        </span>
                                                    <?php else : ?>
                                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold" title="Langganan Aktif">
                                                            <i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i>Aktif <?= $jt ? '(s.d ' . date('d/m/Y', strtotime($jt)) . ')' : ''; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center pe-3">
                                                <div class="d-flex align-items-center justify-content-center gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border text-info btn-detail-outlet rounded-3 px-2 py-1" data-id="<?= $row['id_outlet']; ?>" title="Lihat Detail">
                                                        <i class="fa-light fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border text-warning btn-edit-outlet rounded-3 px-2 py-1" data-id="<?= $row['id_outlet']; ?>" title="Edit Outlet">
                                                        <i class="fa-light fa-pen-to-square"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border text-danger btn-delete-outlet rounded-3 px-2 py-1" data-id="<?= $row['id_outlet']; ?>" data-nama="<?= htmlspecialchars($row['nama_outlet']); ?>" title="Hapus Outlet">
                                                        <i class="fa-light fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fa-light fa-store-slash text-body-secondary mb-3" style="font-size: 60px; opacity: 0.5;"></i>
                                                <h5 class="fw-bold text-body-secondary mb-1">Belum Ada Outlet Terdaftar</h5>
                                                <p class="text-body-secondary small mb-3">Tidak ada outlet yang sesuai dengan kriteria filter pendaftaran.</p>
                                                <button type="button" class="btn btn-danger btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahOutlet">
                                                    <i class="fa-solid fa-plus me-1"></i> Tambah Outlet Baru
                                                </button>
                                            </div>
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
                                Menampilkan <span class="text-body-emphasis fw-bold"><?= ($totalRecords > 0) ? ($offset + 1) : 0; ?></span> - <span class="text-body-emphasis fw-bold"><?= min($offset + $limit, $totalRecords); ?></span> dari <span class="text-body-emphasis fw-bold"><?= $totalRecords; ?></span> outlet terdaftar
                            </div>

                            <?php if ($totalPages > 1) : ?>
                                <nav aria-label="Navigasi Halaman Outlet">
                                    <ul class="pagination pagination-sm mb-0">
                                        <!-- Previous Page -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-start-pill text-body-emphasis px-3" href="<?= buildOutletPageUrl($page - 1, $selectedTgl, $selectedBulan, $selectedTahun); ?>">
                                                <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                            </a>
                                        </li>

                                        <!-- Page Numbers -->
                                        <?php for ($p = 1; $p <= $totalPages; $p++) : ?>
                                            <li class="page-item <?= ($p === $page) ? 'active' : ''; ?>">
                                                <a class="page-link <?= ($p === $page) ? 'bg-danger border-danger text-white fw-bold' : 'text-body-emphasis'; ?>" href="<?= buildOutletPageUrl($p, $selectedTgl, $selectedBulan, $selectedTahun); ?>"><?= $p; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next Page -->
                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-end-pill text-body-emphasis px-3" href="<?= buildOutletPageUrl($page + 1, $selectedTgl, $selectedBulan, $selectedTahun); ?>">
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

    <!-- Jarak Aman Tambahan Sebelum Footer di Mobile -->
    <div class="pb-4 pb-md-5"></div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH OUTLET (WIZARD 2 SESI - PIXEL PERFECT GRID) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTambahOutlet" tabindex="-1" aria-labelledby="modalTambahOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 540px;">
        <div class="modal-content border-0 shadow-lg bg-body" style="border-radius: 20px;">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom border-body-subtle py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="modal-title fw-extrabold text-body-emphasis mb-0 fs-6" id="modalTambahOutletLabel">
                        <i class="fa-solid fa-store me-2 text-danger"></i>Mendaftarkan Outlet Baru
                    </h6>
                    <small class="text-body-secondary" style="font-size: 11px;" id="modalSubtitleWizard">Sesi 1 dari 2: Identitas &amp; Alamat Toko</small>
                </div>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Step Progress Indicator Bar -->
            <div class="px-4 py-2.5 bg-body-tertiary border-bottom border-body-subtle">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <!-- Step 1 Badge -->
                    <div id="stepTab1" class="d-flex align-items-center gap-2 fw-bold text-danger">
                        <span id="badgeStep1" class="badge rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 11px;">1</span>
                        <span style="font-size: 12px;">Profil &amp; Alamat Toko</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-body-tertiary" style="font-size: 10px;"></i>
                    <!-- Step 2 Badge -->
                    <div id="stepTab2" class="d-flex align-items-center gap-2 fw-semibold text-body-tertiary opacity-75">
                        <span id="badgeStep2" class="badge rounded-circle bg-body-secondary text-body-secondary d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 11px;">2</span>
                        <span style="font-size: 12px;">Akun Kasir &amp; Pembayaran</span>
                    </div>
                </div>
            </div>

            <form id="formTambahOutlet" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body p-4">
                    
                    <!-- ========================================== -->
                    <!-- SESI 1: INFORMASI IDENTITAS & ALAMAT TOKO -->
                    <!-- ========================================== -->
                    <div id="stepSection1">
                        <div class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-3 py-1 mb-3 text-uppercase" style="font-size: 10px;">
                            <i class="fa-solid fa-store me-1"></i> Sesi 1: Identitas Toko &amp; Alamat
                        </div>

                        <div class="row g-3">
                            <!-- Nama Outlet Toko -->
                            <div class="col-12">
                                <label class="form-label required">Nama Outlet Toko</label>
                                <input type="text" name="nama_outlet" id="wizard_nama_outlet" class="form-control rounded-3" placeholder="Contoh: Toko Madura Sidoarjo" required>
                            </div>

                            <!-- Nama Pengelola & No WhatsApp (Strict 50%-50%) -->
                            <div class="col-6">
                                <label class="form-label required">Pengelola / Kasir</label>
                                <input type="text" name="nama_pengelola" id="wizard_nama_pengelola" class="form-control rounded-3" placeholder="Budi Santoso" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label required">No. WhatsApp</label>
                                <input type="text" name="no_hp" id="wizard_no_hp" class="form-control rounded-3" placeholder="081234567890" required>
                            </div>

                            <!-- Kecamatan & Alamat Lengkap Toko (Strict 50%-50%) -->
                            <div class="col-6">
                                <label class="form-label required">Kecamatan</label>
                                <input type="text" name="kecamatan" id="wizard_kecamatan" class="form-control rounded-3" placeholder="Taman" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label required">Alamat Lengkap Toko</label>
                                <input type="text" name="alamat_outlet" id="wizard_alamat_outlet" class="form-control rounded-3" placeholder="Jl. Raya Taman No. 12" required>
                            </div>

                            <!-- Persentase Bagi Hasil Investor -->
                            <div class="col-12">
                                <label class="form-label required">Persentase Bagi Hasil Investor (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="100" name="persentase_potongan" id="wizard_persentase_potongan" class="form-control rounded-start-3 fw-bold" placeholder="10.00" value="10.00" required>
                                    <span class="input-group-text bg-body-tertiary text-body-secondary fw-bold px-2.5">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden rounded-end-3 bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 28px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center btn-percent-up" onclick="stepPercentUp()" style="font-size: 9px; line-height: 1;" title="Tambah Persen (+1%)">
                                                <i class="fa-solid fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center btn-percent-down" onclick="stepPercentDown()" style="font-size: 9px; line-height: 1;" title="Kurangi Persen (-1%)">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text text-body-secondary mt-1" style="font-size: 11px;">Gunakan tombol ▲ / ▼ di sebelah kanan atau ketik langsung persentase potongan investor.</div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- SESI 2: AKUN KASIR & INFORMASI PEMBAYARAN -->
                    <!-- ========================================== -->
                    <div id="stepSection2" class="d-none">
                        <div class="badge bg-success-subtle text-success fw-bold rounded-pill px-3 py-1 mb-3 text-uppercase" style="font-size: 10px;">
                            <i class="fa-solid fa-key me-1"></i> Sesi 2: Akun Kasir &amp; Upload Bukti Transfer
                        </div>

                        <div class="row g-3">
                            <!-- Username & Password Kasir (Strict 50%-50%) -->
                            <div class="col-6">
                                <label class="form-label required">Username Login Kasir</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary px-2">@</span>
                                    <input type="text" name="username" id="wizard_username" class="form-control rounded-end-3 border-start-0 ps-1" placeholder="outlet_sidoarjo" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label required">Password Login Kasir</label>
                                <input type="password" name="password" id="wizard_password" class="form-control rounded-3" placeholder="Password akun" required>
                            </div>

                            <!-- Payment Info Box -->
                            <div class="col-12">
                                <div class="card border-0 bg-danger-subtle text-danger-emphasis p-3 rounded-3 shadow-xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <span class="fw-bold" style="font-size: 12px;"><i class="fa-solid fa-receipt me-1"></i> Biaya Lisensi Toko</span>
                                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1" style="font-size: 12px;">Rp <?= number_format($biayaLangganan, 0, ',', '.'); ?></span>
                                    </div>
                                    <p class="text-body-secondary mb-0 lh-sm" style="font-size: 11px;">
                                        Transfer ke <strong>Bank <?= htmlspecialchars($bankNama); ?>: <?= htmlspecialchars($bankNoRek); ?></strong> a.n. <strong><?= htmlspecialchars($bankAtasNama); ?></strong>.
                                    </p>
                                </div>
                            </div>

                            <!-- Upload Bukti Pembayaran -->
                            <div class="col-12">
                                <label class="form-label required">Upload Bukti Transfer Pembayaran</label>
                                <input type="file" name="bukti_pembayaran" id="wizard_bukti_pembayaran" class="form-control rounded-3" accept="image/*,.pdf" required>
                                <div class="form-text text-body-secondary mt-1" style="font-size: 11px;">Format: JPG, PNG, WEBP, atau PDF (Max 5MB).</div>
                            </div>

                            <!-- Informational Alert Box -->
                            <div class="col-12">
                                <div class="alert alert-info border-0 rounded-3 p-3 mb-0">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fa-solid fa-circle-info text-info fs-6 flex-shrink-0 mt-0.5"></i>
                                        <small class="text-body-secondary lh-sm" style="font-size: 11px;">
                                            Outlet baru akan diverifikasi dan diaktifkan oleh Admin setelah bukti pendaftaran dikirim.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer Navigation Controls -->
                <div class="modal-footer border-top border-body-subtle py-3 px-4 d-flex justify-content-between align-items-center">
                    <!-- Step 1 Footer Buttons -->
                    <div id="footerStep1" class="d-flex align-items-center justify-content-between w-100">
                        <button type="button" class="btn btn-light rounded-pill px-3.5 py-2" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger rounded-pill px-4 py-2 fw-bold" onclick="goToWizardStep(2)">
                            Lanjut Ke Pembayaran <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                    </div>

                    <!-- Step 2 Footer Buttons -->
                    <div id="footerStep2" class="d-flex align-items-center justify-content-between w-100 d-none">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold" onclick="goToWizardStep(1)">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold">
                            <i class="fa-solid fa-paper-plane me-1"></i> Kirim Pendaftaran
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT OUTLET (Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditOutlet" tabindex="-1" aria-labelledby="modalEditOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalEditOutletLabel">
                    <i class="fa-light fa-pen-to-square me-2 text-warning"></i>Edit Data Outlet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditOutlet" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_outlet" id="edit_id_outlet" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Nama Outlet</label>
                        <input type="text" name="nama_outlet" id="edit_nama_outlet" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary">Alamat Outlet</label>
                        <textarea name="alamat_outlet" id="edit_alamat_outlet" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                    <hr class="my-3 text-body-secondary opacity-25">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Username Login Outlet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary">@</span>
                            <input type="text" name="username" id="edit_username" class="form-control rounded-end-3 border-start-0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary">Password Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control rounded-3" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Outlet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL OUTLET (Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDetailOutlet" tabindex="-1" aria-labelledby="modalDetailOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalDetailOutletLabel">
                    <i class="fa-light fa-circle-info me-2 text-info"></i>Rincian Informasi Outlet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="detailOutletLoading" class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="detailOutletContent" class="d-none">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger p-3 mb-2" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-store fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1 text-body-emphasis" id="det_nama_outlet">-</h4>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill" id="det_created_at_badge"><i class="fa-regular fa-clock me-1"></i> Terdaftar: -</span>
                    </div>

                    <div class="list-group list-group-flush rounded-3 border border-body-subtle mb-3">
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-user me-2 text-danger"></i>Username Akun Login</span>
                            <span class="fw-bold text-body-emphasis" id="det_username">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-regular fa-clock me-2 text-primary"></i>Waktu Pendaftaran</span>
                            <span class="fw-bold text-body-emphasis" id="det_created_at_full">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-location-dot me-2 text-danger"></i>Alamat Outlet</span>
                            <span class="fw-semibold text-body-emphasis text-end" id="det_alamat">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-receipt me-2 text-success"></i>Bukti Transfer Bayar</span>
                            <span id="det_bukti_container" class="fw-semibold text-body-emphasis">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-file-invoice me-2 text-primary"></i>Total Laporan Omzet</span>
                            <span class="fw-bold text-body-emphasis" id="det_total_laporan">0 Laporan</span>
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

<!-- ========================================================================= -->
<!-- MODAL: FILTER TANGGAL PEMBUATAN OUTLET (Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalFilterOutlet" tabindex="-1" aria-labelledby="modalFilterOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalFilterOutletLabel">
                    <i class="fa-solid fa-filter me-2 text-danger"></i>Filter Tanggal Pendaftaran Outlet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="<?= SystemInfo::app('CLIENT_URL'); ?>/outlet">
                <div class="modal-body p-4">
                    <!-- 1. Filter Tanggal Spesifik Dibuat -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-body-secondary"><i class="fa-regular fa-calendar me-1 text-danger"></i>Tanggal Dibuat (Hari/Bulan/Tahun)</label>
                        <input type="date" name="tgl" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold" value="<?= htmlspecialchars($selectedTgl); ?>" title="Filter Tanggal Dibuat">
                    </div>

                    <!-- 2. Filter Bulan & Tahun Dibuat -->
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-body-secondary"><i class="fa-regular fa-calendar-days me-1 text-danger"></i>Bulan Dibuat</label>
                            <select name="bulan" class="form-select bg-body border-body-subtle text-body-emphasis fw-semibold">
                                <option value="0" <?= ($selectedBulan === 0) ? 'selected' : ''; ?>>Semua Bulan</option>
                                <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                    <option value="<?= $mNum; ?>" <?= ($selectedBulan === $mNum) ? 'selected' : ''; ?>><?= $mName; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-body-secondary"><i class="fa-regular fa-calendar-lines me-1 text-danger"></i>Tahun Dibuat</label>
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

<script>
$(document).ready(function() {
    const ACTION_URL = '<?= SystemInfo::app('CLIENT_URL'); ?>/doc/outlet/action.php';

    // 0. Live Search Real-Time Filter for Outlet Table
    $('#liveSearchOutlet').on('keyup input', function() {
        const val = $(this).val().toLowerCase().trim();
        let matchCount = 0;

        $('#tableDataOutlet tbody tr.outlet-data-row').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.indexOf(val) > -1) {
                $(this).removeClass('d-none').show();
                matchCount++;
            } else {
                $(this).addClass('d-none').hide();
            }
        });

        if (val.length > 0 && matchCount === 0) {
            if ($('#rowLiveSearchEmpty').length === 0) {
                $('#tableDataOutlet tbody').append(`
                    <tr id="rowLiveSearchEmpty">
                        <td colspan="7" class="text-center py-4 text-danger fw-semibold">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> Outlet "${val}" tidak ditemukan.
                        </td>
                    </tr>
                `);
            } else {
                $('#rowLiveSearchEmpty').removeClass('d-none').show().find('td').html(`
                    <i class="fa-solid fa-circle-exclamation me-1"></i> Outlet "${val}" tidak ditemukan.
                `);
            }
        } else {
            $('#rowLiveSearchEmpty').remove();
        }

        if (val.length === 0) {
            $('#tableDataOutlet tbody tr.outlet-data-row').removeClass('d-none').show();
            $('#rowLiveSearchEmpty').remove();
        }
    });

    // Up & Down Spinner Functions for Persentase Bagi Hasil Investor
    window.stepPercentUp = function() {
        let el = $('#wizard_persentase_potongan');
        let val = parseFloat(el.val()) || 0;
        if (val < 100) {
            val = Math.min(100, val + 1);
            el.val(val.toFixed(2));
        }
    };

    window.stepPercentDown = function() {
        let el = $('#wizard_persentase_potongan');
        let val = parseFloat(el.val()) || 0;
        if (val > 0) {
            val = Math.max(0, val - 1);
            el.val(val.toFixed(2));
        }
    };

    // 0. Wizard Navigation Function for Modal Tambah Outlet (2 Sesi)
    window.goToWizardStep = function(step) {
        if (step === 2) {
            // Validate ALL Step 1 Required Fields
            const nama = $('#wizard_nama_outlet').val().trim();
            const pengelola = $('#wizard_nama_pengelola').val().trim();
            const noHp = $('#wizard_no_hp').val().trim();
            const kec = $('#wizard_kecamatan').val().trim();
            const alamat = $('#wizard_alamat_outlet').val().trim();
            const potongan = $('#wizard_persentase_potongan').val().trim();

            if (!nama) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi Nama Outlet Toko terlebih dahulu.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_nama_outlet').focus());
                return;
            }
            if (!pengelola) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi Nama Pengelola / Kasir terlebih dahulu.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_nama_pengelola').focus());
                return;
            }
            if (!noHp) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi No. HP / WhatsApp pengelola terlebih dahulu.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_no_hp').focus());
                return;
            }
            if (!kec) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi Kecamatan outlet terlebih dahulu.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_kecamatan').focus());
                return;
            }
            if (!alamat) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi Alamat Lengkap Toko terlebih dahulu.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_alamat_outlet').focus());
                return;
            }
            if (!potongan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi Persentase Bagi Hasil Investor.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_persentase_potongan').focus());
                return;
            }

            // Transition to Step 2
            $('#stepSection1').addClass('d-none');
            $('#stepSection2').removeClass('d-none');
            $('#footerStep1').addClass('d-none');
            $('#footerStep2').removeClass('d-none');

            // Update Indicator
            $('#modalSubtitleWizard').text('Sesi 2 dari 2: Akun Kasir & Pembayaran Lisensi');
            $('#stepTab1').removeClass('text-danger').addClass('text-success');
            $('#badgeStep1').removeClass('bg-danger').addClass('bg-success').html('<i class="fa-solid fa-check"></i>');
            
            $('#stepTab2').removeClass('text-body-tertiary opacity-75').addClass('text-danger fw-bold');
            $('#badgeStep2').removeClass('bg-body-secondary text-body-secondary').addClass('bg-danger text-white shadow-xs');
        } else {
            // Return to Step 1
            $('#stepSection2').addClass('d-none');
            $('#stepSection1').removeClass('d-none');
            $('#footerStep2').addClass('d-none');
            $('#footerStep1').removeClass('d-none');

            // Reset Indicator
            $('#modalSubtitleWizard').text('Sesi 1 dari 2: Identitas & Alamat Toko');
            $('#stepTab1').removeClass('text-success').addClass('text-danger');
            $('#badgeStep1').removeClass('bg-success').addClass('bg-danger').text('1');
            
            $('#stepTab2').removeClass('text-danger fw-bold').addClass('text-body-tertiary opacity-75');
            $('#badgeStep2').removeClass('bg-danger text-white shadow-xs').addClass('bg-body-secondary text-body-secondary').text('2');
        }
    };

    // Reset Wizard on Modal Close
    $('#modalTambahOutlet').on('hidden.bs.modal', function () {
        goToWizardStep(1);
        $('#formTambahOutlet')[0].reset();
    });

    // 1. Submit Form Tambah Outlet & Bukti Transfer
    $('#formTambahOutlet').on('submit', function(e) {
        e.preventDefault();

        // Validate Step 2 Required Fields
        const user = $('#wizard_username').val().trim();
        const pass = $('#wizard_password').val().trim();
        const fileProof = $('#wizard_bukti_pembayaran')[0].files;

        if (!user) {
            Swal.fire({
                icon: 'warning',
                title: 'Form Belum Lengkap',
                text: 'Harap isi Username Login Kasir terlebih dahulu.',
                confirmButtonColor: '#7D0A0A'
            }).then(() => $('#wizard_username').focus());
            return;
        }
        if (!pass) {
            Swal.fire({
                icon: 'warning',
                title: 'Form Belum Lengkap',
                text: 'Harap isi Password Login Kasir terlebih dahulu.',
                confirmButtonColor: '#7D0A0A'
            }).then(() => $('#wizard_password').focus());
            return;
        }
        if (fileProof.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Form Belum Lengkap',
                text: 'Harap upload file Bukti Transfer Pembayaran terlebih dahulu.',
                confirmButtonColor: '#7D0A0A'
            }).then(() => $('#wizard_bukti_pembayaran').focus());
            return;
        }

        const form = $(this);
        const formData = new FormData(this);
        const submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Mengirim Request...');

        $.ajax({
            url: ACTION_URL,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Request & Pembayaran');
                if (res.success) {
                    $('#modalTambahOutlet').modal('hide');
                    form[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Berhasil Dikirim!',
                        text: res.message,
                        confirmButtonColor: '#7D0A0A'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Request & Pembayaran');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat mengirim request.', 'error');
            }
        });
    });

    // 2. Fetch Detail for Edit Outlet
    $(document).on('click', '.btn-edit-outlet', function() {
        const idOutlet = $(this).data('id');
        
        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_outlet: idOutlet },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#edit_id_outlet').val(res.data.id_outlet);
                    $('#edit_nama_outlet').val(res.data.nama_outlet);
                    $('#edit_alamat_outlet').val(res.data.alamat_outlet);
                    $('#edit_username').val(res.data.username);
                    $('#modalEditOutlet').modal('show');
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal mengambil data outlet.', 'error');
            }
        });
    });

    // 3. Submit Edit Outlet
    $('#formEditOutlet').on('submit', function(e) {
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
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Update Outlet');
                if (res.success) {
                    $('#modalEditOutlet').modal('hide');
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
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Update Outlet');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat memperbarui data.', 'error');
            }
        });
    });

    // 4. Fetch Detail for View Outlet
    $(document).on('click', '.btn-detail-outlet', function() {
        const idOutlet = $(this).data('id');
        $('#detailOutletLoading').removeClass('d-none');
        $('#detailOutletContent').addClass('d-none');
        $('#modalDetailOutlet').modal('show');

        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_outlet: idOutlet },
            dataType: 'json',
            success: function(res) {
                $('#detailOutletLoading').addClass('d-none');
                if (res.success) {
                    let formattedCreated = res.data.tanggal_bergabung ? res.data.tanggal_bergabung : (res.data.created_at ? res.data.created_at : '-');
                    $('#det_nama_outlet').text(res.data.nama_outlet);
                    $('#det_created_at_badge').html('<i class="fa-regular fa-clock me-1"></i> Terdaftar: ' + formattedCreated);
                    $('#det_created_at_full').text(formattedCreated);
                    $('#det_username').text('@' + res.data.username);
                    $('#det_alamat').text(res.data.alamat_outlet || '-');
                    if (res.data.bukti_pembayaran) {
                        let fileUrl = '<?= SystemInfo::app("CLIENT_URL"); ?>/' + res.data.bukti_pembayaran;
                        $('#det_bukti_container').html('<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 text-decoration-none"><i class="fa-solid fa-file-arrow-down me-1"></i> Lihat Bukti Bayar</a>');
                    } else {
                        $('#det_bukti_container').text('-');
                    }
                    $('#det_total_omzet').text('Rp ' + new Intl.NumberFormat('id-ID').format(res.data.total_omzet));
                    $('#det_total_laporan').text(res.data.total_laporan + ' Laporan');
                    $('#detailOutletContent').removeClass('d-none');
                } else {
                    $('#modalDetailOutlet').modal('hide');
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                $('#modalDetailOutlet').modal('hide');
                Swal.fire('Error', 'Gagal mengambil rincian data outlet.', 'error');
            }
        });
    });

    // 4.5. Show SweetAlert Detail Alamat Lengkap Toko
    $(document).on('click', '.btn-detail-alamat', function(e) {
        e.preventDefault();
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');

        Swal.fire({
            title: `<i class="fa-solid fa-location-dot text-danger me-2"></i>Alamat Lengkap Toko`,
            html: `
                <div class="text-start bg-body-tertiary p-3 rounded-3 border">
                    <div class="mb-2">
                        <small class="text-body-secondary fw-semibold d-block">Nama Outlet:</small>
                        <strong class="text-body-emphasis">${nama}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-body-secondary fw-semibold d-block">Kecamatan:</small>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-2 fw-bold">${kec}</span>
                    </div>
                    <div>
                        <small class="text-body-secondary fw-semibold d-block">Alamat Lengkap Toko:</small>
                        <p class="mb-0 text-body-emphasis lh-sm fw-semibold">${alamat}</p>
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A'
        });
    });

    // 5. Delete Outlet with SweetAlert2
    $(document).on('click', '.btn-delete-outlet', function() {
        const idOutlet = $(this).data('id');
        const namaOutlet = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Outlet?',
            text: `Apakah Anda yakin ingin menghapus outlet "${namaOutlet}" beserta seluruh akun loginnya?`,
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
                    data: { action: 'delete', id_outlet: idOutlet },
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
                        Swal.fire('Error', 'Gagal menghapus outlet.', 'error');
                    }
                });
            }
        });
    });

    // 6. Cek Alasan Penolakan Outlet (SweetAlert2)
    $(document).on('click', '.btn-cek-alasan', function() {
        const nama = $(this).data('nama');
        const alasan = $(this).data('alasan');

        Swal.fire({
            title: '<div class="text-danger fw-extrabold fs-5"><i class="fa-solid fa-circle-xmark me-2"></i>Alasan Penolakan Outlet</div>',
            html: `
                <div class="text-start p-3 bg-light rounded-3 border border-danger-subtle mt-2 mb-1">
                    <div class="text-secondary small fw-bold mb-1">Nama Toko:</div>
                    <div class="fw-bold text-dark mb-3 fs-6">${nama}</div>
                    <div class="text-secondary small fw-bold mb-1"><i class="fa-solid fa-comment-dots text-danger me-1"></i>Catatan Alasan Admin:</div>
                    <div class="p-2.5 bg-white rounded-3 border border-danger-subtle text-danger fw-semibold" style="font-size: 13.5px; line-height: 1.5; white-space: pre-wrap;">
                        "${alasan}"
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#6c757d',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });
});
</script>
