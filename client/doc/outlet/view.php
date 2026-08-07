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
    $limitM  = 10;
    $pageM   = isset($_GET['page_m']) ? max(1, (int)$_GET['page_m']) : 1;
    $offsetM = ($pageM - 1) * $limitM;

    $resTotalM = $db->query("
        SELECT COUNT(DISTINCT o.id_outlet) as total
        FROM outlet o
        JOIN investor i ON i.id_investor = o.id_investor
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
    ");
    $totalRecordsM = ($resTotalM && $rowTM = $resTotalM->fetch_assoc()) ? (int)$rowTM['total'] : 0;
    $totalPagesM   = ceil($totalRecordsM / $limitM);

    $listMasterOutlets = $db->query("
        SELECT o.id_outlet, o.nama_outlet, o.kecamatan as kecamatan_outlet, o.alamat_outlet, o.tanggal_bergabung,
               o.status, o.tgl_jatuh_tempo, o.tipe_request,
               u_inv.nama_lengkap as nama_investor, i.kecamatan as kecamatan_investor, i.alamat_investor, u_out.username as username_outlet
        FROM outlet o
        JOIN investor i ON i.id_investor = o.id_investor
        JOIN users u_inv ON u_inv.id_users = i.id_users
        LEFT JOIN users u_out ON u_out.id_users = o.id_users
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
        ORDER BY o.id_outlet DESC
        LIMIT {$limitM} OFFSET {$offsetM}
    ");
    $totalOutletMaster = $totalRecordsM;

    if (!function_exists('buildMasterOutletPageUrl')) {
        function buildMasterOutletPageUrl($p) {
            $params = $_GET;
            $params['page_m'] = $p;
            return '?' . http_build_query($params);
        }
    }
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
                        <table id="table-master-outlet-sub" class="table table-hover align-middle mb-0 w-100">
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
                                                <div class="mt-1">
                                                    <button type="button" class="btn btn-xs btn-outline-success btn-detail-alamat-outlet rounded-pill px-2.5 py-1 shadow-xs fw-bold" style="font-size: 10.5px;"
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>"
                                                            data-kecamatan="<?= htmlspecialchars($row['kecamatan_outlet'] ?: '-') ?>"
                                                            data-alamat="<?= htmlspecialchars($row['alamat_outlet'] ?: '-') ?>">
                                                        <i class="fa-solid fa-location-dot me-1"></i>Detail Alamat
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
                                                <?php 
                                                $todayM = date('Y-m-d');
                                                $jtM = !empty($row['tgl_jatuh_tempo']) ? date('Y-m-d', strtotime($row['tgl_jatuh_tempo'])) : null;
                                                $daysRemainingM = $jtM ? (int)((strtotime($jtM) - strtotime($todayM)) / 86400) : 999;
                                                $isExpiredM = ($jtM && $todayM > $jtM);
                                                $isNearExpiryM = ($jtM && !$isExpiredM && $daysRemainingM <= 7);
                                                $isPendingRenewM = (($row['status'] ?? '') === 'pending' && ($row['tipe_request'] ?? '') === 'perpanjangan');
                                                $isPendingNewM = (($row['status'] ?? '') === 'pending' && ($row['tipe_request'] ?? '') !== 'perpanjangan');
                                                $isRejectRenewM = (($row['status'] ?? '') === 'reject' && ($row['tipe_request'] ?? '') === 'perpanjangan');
                                                $isRejectNewM = (($row['status'] ?? '') === 'reject' && ($row['tipe_request'] ?? '') !== 'perpanjangan');
                                                ?>

                                                <?php if ($isPendingRenewM) : ?>
                                                    <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-clock-rotate-left me-1 text-warning"></i>Pending Perpanjangan
                                                    </span>
                                                <?php elseif ($isPendingNewM) : ?>
                                                    <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-regular fa-clock me-1 text-warning"></i>Pending Pendaftaran
                                                    </span>
                                                <?php elseif ($isRejectRenewM) : ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-circle-xmark me-1"></i>Perpanjangan Ditolak
                                                    </span>
                                                <?php elseif ($isRejectNewM) : ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-circle-xmark me-1"></i>Pendaftaran Ditolak
                                                    </span>
                                                <?php elseif ($isExpiredM) : ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Expired
                                                    </span>
                                                <?php elseif ($isNearExpiryM) : ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-clock me-1"></i>Akan Kadaluarsa (<?= $daysRemainingM ?> hari)
                                                    </span>
                                                <?php else : ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-circle-check me-1"></i>Active
                                                    </span>
                                                <?php endif; ?>
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

                    <!-- Pagination Controls & Record Summary Footer -->
                    <?php if ($totalRecordsM > 0) : ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-body-subtle mt-2">
                            <div class="small text-body-secondary fw-semibold ms-1">
                                Menampilkan <span class="text-body-emphasis fw-bold"><?= ($totalRecordsM > 0) ? ($offsetM + 1) : 0; ?></span> - <span class="text-body-emphasis fw-bold"><?= min($offsetM + $limitM, $totalRecordsM); ?></span> dari <span class="text-body-emphasis fw-bold"><?= $totalRecordsM; ?></span> outlet terikat
                            </div>

                            <?php if ($totalPagesM > 1) : ?>
                                <nav aria-label="Navigasi Halaman Outlet Master">
                                    <ul class="pagination pagination-sm mb-0">
                                        <!-- Previous Page -->
                                        <li class="page-item <?= ($pageM <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-start-pill text-body-emphasis px-3" href="<?= buildMasterOutletPageUrl($pageM - 1); ?>">
                                                <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                            </a>
                                        </li>

                                        <!-- Page Numbers -->
                                        <?php for ($p = 1; $p <= $totalPagesM; $p++) : ?>
                                            <li class="page-item <?= ($p == $pageM) ? 'active' : ''; ?>">
                                                <a class="page-link text-body-emphasis px-3" href="<?= buildMasterOutletPageUrl($p); ?>"><?= $p; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next Page -->
                                        <li class="page-item <?= ($pageM >= $totalPagesM) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-end-pill text-body-emphasis px-3" href="<?= buildMasterOutletPageUrl($pageM + 1); ?>">
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

// Filter Data Outlet Toko (Rentang Tanggal Pendaftaran: tgl_mulai & tgl_selesai)
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';

// Fetch system settings (fee & bank details) from pengaturan_sistem
$sysSettings = [];
$resSysSetting = $db->query("SELECT nama_pengaturan, nilai FROM pengaturan_sistem");
if ($resSysSetting) {
    while ($r = $resSysSetting->fetch_assoc()) {
        $sysSettings[$r['nama_pengaturan']] = $r['nilai'];
    }
}
$resInvFee = $db->query("SELECT biaya_langganan_outlet FROM investor WHERE id_investor = {$investorId} LIMIT 1");
$biayaLangganan = 100000.00;
if ($resInvFee && $resInvFee->num_rows > 0) {
    $rowFee = $resInvFee->fetch_assoc();
    if (!empty($rowFee['biaya_langganan_outlet']) && (float)$rowFee['biaya_langganan_outlet'] > 0) {
        $biayaLangganan = (float)$rowFee['biaya_langganan_outlet'];
    }
}
$bankNama       = $sysSettings['bank_nama'] ?? 'BCA';
$bankNoRek      = $sysSettings['bank_no_rekening'] ?? '123-456-7890';
$bankAtasNama   = $sysSettings['bank_atas_nama'] ?? 'Toko Madura Pusat';

// Build WHERE clause for Outlet Registration Date & Ownership
$whereOutletConds = ["o.id_investor = {$investorId}"];

if (!empty($selectedTglMulai) && !empty($selectedTglSelesai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereOutletConds[] = "DATE(o.tanggal_bergabung) BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
} elseif (!empty($selectedTglMulai)) {
    $safeMulai = $db->real_escape_string($selectedTglMulai);
    $whereOutletConds[] = "DATE(o.tanggal_bergabung) >= '{$safeMulai}'";
} elseif (!empty($selectedTglSelesai)) {
    $safeSelesai = $db->real_escape_string($selectedTglSelesai);
    $whereOutletConds[] = "DATE(o.tanggal_bergabung) <= '{$safeSelesai}'";
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
$totalRecords = 0;
if ($resCount) {
    $totalRecords = (int)$resCount->fetch_assoc()['total'];
}

$totalPages = ($totalRecords > 0) ? (int)ceil($totalRecords / $limit) : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $limit;

// Fetch Outlets Matching Filter with Pagination & Status
$sqlOutlets = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        o.kecamatan,
        o.alamat_outlet,
        o.persentase_potongan,
        o.persen_bagian_investor,
        o.tanggal_bergabung,
        o.status,
        o.tipe_request,
        o.tgl_jatuh_tempo,
        o.alasan_penolakan,
        o.bukti_pembayaran,
        o.tanggal_request,
        u.nama_lengkap,
        u.no_hp,
        u.username
    FROM outlet o
    JOIN users u ON o.id_users = u.id_users
    {$whereOutletSql}
    ORDER BY o.tanggal_bergabung DESC, o.id_outlet DESC
    LIMIT {$limit} OFFSET {$offset}
";

$resOutlets = $db->query($sqlOutlets);
$outlets = [];
if ($resOutlets) {
    while ($row = $resOutlets->fetch_assoc()) {
        $outlets[] = $row;
    }
}

// Total Outlets count for Investor
$resTotalAll = $db->query("SELECT COUNT(*) as cnt FROM outlet WHERE id_investor = {$investorId}");
$totalOutlet = $resTotalAll ? (int)$resTotalAll->fetch_assoc()['cnt'] : 0;

function buildOutletPageUrl($pageNum, $selectedTglMulai, $selectedTglSelesai) {
    $params = ['page' => $pageNum];
    if (!empty($selectedTglMulai)) $params['tgl_mulai'] = $selectedTglMulai;
    if (!empty($selectedTglSelesai)) $params['tgl_selesai'] = $selectedTglSelesai;
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
}
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
                        <h5 class="fw-bold text-body-emphasis mb-1 fs-6">
                            <i class="fa-solid fa-store me-2 text-danger"></i>Daftar Outlet Terdaftar
                            <?php if (!empty($selectedTglMulai) || !empty($selectedTglSelesai)) : ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2 fw-bold" style="font-size: 10px;">
                                    <i class="fa-solid fa-calendar-range me-1"></i>
                                    <?= !empty($selectedTglMulai) ? date('d/m/Y', strtotime($selectedTglMulai)) : 'Awal'; ?> s/d <?= !empty($selectedTglSelesai) ? date('d/m/Y', strtotime($selectedTglSelesai)) : 'Akhir'; ?>
                                </span>
                            <?php endif; ?>
                        </h5>
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
                                                <span class="badge bg-body-tertiary border text-body-emphasis px-2 py-1 rounded-3 fw-semibold font-monospace small">
                                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                    <?= !empty($row['tanggal_bergabung']) ? date('d/m/Y H:i', strtotime($row['tanggal_bergabung'])) . ' WIB' : '-'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-3 py-1.5 btn-detail-alamat shadow-xs fw-bold" 
                                                    data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-kecamatan="<?= htmlspecialchars($row['kecamatan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-alamat="<?= htmlspecialchars($row['alamat_outlet'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                    style="font-size: 11px;">
                                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>Detail Alamat
                                                </button>
                                            </td>
                                            <td>
                                                <?php 
                                                $today = date('Y-m-d');
                                                $jt = !empty($row['tgl_jatuh_tempo']) ? date('Y-m-d', strtotime($row['tgl_jatuh_tempo'])) : null;
                                                $daysRemaining = $jt ? (int)((strtotime($jt) - strtotime($today)) / 86400) : 999;
                                                $isExpired = ($jt && $today > $jt);
                                                $isNearExpiry = ($jt && !$isExpired && $daysRemaining <= 7);
                                                $isPendingRenewal = (($row['status'] ?? '') === 'pending' && ($row['tipe_request'] ?? '') === 'perpanjangan');
                                                $isPendingNew = (($row['status'] ?? '') === 'pending' && ($row['tipe_request'] ?? '') !== 'perpanjangan');
                                                ?>

                                                <?php if ($isPendingRenewal || $isPendingNew) : ?>
                                                    <?php 
                                                    $tglPengajuanFormatted = !empty($row['tanggal_bergabung']) ? date('d/m/Y H:i', strtotime($row['tanggal_bergabung'])) . ' WIB' : (!empty($row['tanggal_request']) ? date('d/m/Y H:i', strtotime($row['tanggal_request'])) . ' WIB' : '-');
                                                    $buktiUrl = !empty($row['bukti_pembayaran']) ? (SystemInfo::app('CLIENT_URL') . '/' . $row['bukti_pembayaran']) : '';
                                                    ?>
                                                    <div class="d-flex flex-column align-items-center justify-content-center py-2 mx-auto" style="width: 170px; min-height: 56px; gap: 6px;">
                                                        <span class="badge bg-warning-subtle text-dark border border-warning px-2.5 py-1 rounded-pill fw-bold w-100 text-center shadow-xs" style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="<?= $isPendingRenewal ? 'fa-solid fa-clock-rotate-left' : 'fa-regular fa-clock'; ?> me-1 text-warning"></i><?= $isPendingRenewal ? 'Verifikasi Perpanjangan' : 'Verifikasi Pendaftaran'; ?>
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark border-warning py-0.5 px-2.5 rounded-pill btn-detail-pending fw-bold w-100 text-center shadow-xs"
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-tipe="<?= $isPendingRenewal ? 'Perpanjangan Langganan' : 'Pendaftaran Baru'; ?>"
                                                            data-waktu="<?= htmlspecialchars($tglPengajuanFormatted, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-bukti="<?= htmlspecialchars($buktiUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                            style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="fa-solid fa-circle-info me-1 text-warning"></i>Detail Pengajuan
                                                        </button>
                                                    </div>
                                                <?php elseif (($row['status'] ?? '') === 'reject') : ?>
                                                    <?php $isRejectRenew = (($row['tipe_request'] ?? '') === 'perpanjangan'); ?>
                                                    <div class="d-flex flex-column align-items-center justify-content-center py-2 mx-auto" style="width: 170px; min-height: 56px; gap: 6px;">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-bold w-100 text-center shadow-xs" style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="fa-solid fa-circle-xmark me-1"></i><?= $isRejectRenew ? 'Perpanjangan Ditolak' : 'Pendaftaran Ditolak'; ?>
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2.5 rounded-pill btn-cek-alasan fw-bold w-100 text-center shadow-xs"
                                                            data-id="<?= htmlspecialchars($row['id_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-alasan="<?= htmlspecialchars($row['alasan_penolakan'] ?: 'Tidak ada catatan alasan dari admin.', ENT_QUOTES, 'UTF-8'); ?>"
                                                            style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="fa-solid fa-comment-dots me-1"></i>Cek Alasan
                                                        </button>
                                                    </div>
                                                <?php elseif ($isExpired) : ?>
                                                    <div class="d-flex flex-column align-items-center justify-content-center py-2 mx-auto" style="width: 170px; min-height: 56px; gap: 6px;">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-bold w-100 text-center shadow-xs" style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Expired (<?= date('d/m/Y', strtotime($jt)); ?>)
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2.5 rounded-pill btn-trigger-perpanjang fw-bold w-100 text-center shadow-xs"
                                                            data-id="<?= $row['id_outlet']; ?>"
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="fa-solid fa-rotate-right me-1"></i>Perpanjang Langganan
                                                        </button>
                                                    </div>
                                                <?php elseif ($isNearExpiry) : ?>
                                                    <div class="d-flex flex-column align-items-center justify-content-center py-2 mx-auto" style="width: 170px; min-height: 56px; gap: 6px;">
                                                        <span class="badge bg-warning-subtle text-dark border border-warning px-2.5 py-1 rounded-pill fw-bold w-100 text-center shadow-xs" style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="fa-solid fa-triangle-exclamation me-1 text-warning"></i>Aktif (H-<?= $daysRemaining; ?> Expired)
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark border-warning py-0.5 px-2.5 rounded-pill btn-trigger-perpanjang fw-bold w-100 text-center shadow-xs"
                                                            data-id="<?= $row['id_outlet']; ?>"
                                                            data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            style="font-size: 10.5px; letter-spacing: 0.1px;">
                                                            <i class="fa-solid fa-rotate-right me-1 text-warning"></i>Perpanjang Awal
                                                        </button>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex flex-column align-items-center justify-content-center py-1.5 mx-auto" style="width: 170px; min-height: 56px; gap: 6px;">
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold w-100 text-center shadow-xs my-auto" style="font-size: 10.5px; letter-spacing: 0.1px;" title="Langganan Aktif">
                                                            <i class="fa-solid fa-circle me-1 text-success" style="font-size: 8px;"></i>Aktif <?= $jt ? '(s.d ' . date('d/m/Y', strtotime($jt)) . ')' : ''; ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center pe-3">
                                                <div class="d-flex align-items-center justify-content-center gap-1">
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
                                        <td colspan="6" class="text-center py-5">
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
                                            <a class="page-link rounded-start-pill text-body-emphasis px-3" href="<?= buildOutletPageUrl($page - 1, $selectedTglMulai, $selectedTglSelesai); ?>">
                                                <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                            </a>
                                        </li>

                                        <!-- Page Numbers -->
                                        <?php for ($p = 1; $p <= $totalPages; $p++) : ?>
                                            <li class="page-item <?= ($p === $page) ? 'active' : ''; ?>">
                                                <a class="page-link <?= ($p === $page) ? 'bg-danger border-danger text-white fw-bold' : 'text-body-emphasis'; ?>" href="<?= buildOutletPageUrl($p, $selectedTglMulai, $selectedTglSelesai); ?>"><?= $p; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next Page -->
                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-end-pill text-body-emphasis px-3" href="<?= buildOutletPageUrl($page + 1, $selectedTglMulai, $selectedTglSelesai); ?>">
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

                            <!-- Persentase Potongan & Pembagian Hak (3 Kolom Sejajar dengan Spinner) -->
                            <div class="col-4">
                                <label class="form-label required" style="font-size: 11px;">Potongan Omzet (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" name="persentase_potongan" id="wizard_persentase_potongan" class="form-control rounded-start-3 fw-bold" placeholder="10.00" value="10.00" required>
                                    <span class="input-group-text bg-body-tertiary text-body-secondary fw-bold px-2 border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden rounded-end-3 bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 22px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepPotongan(1)" style="font-size: 8px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fa-solid fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepPotongan(-1)" style="font-size: 8px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text text-body-secondary mt-1" style="font-size: 9.5px;">Potongan harian dari omzet.</div>
                            </div>
                            <div class="col-4">
                                <label class="form-label required" style="font-size: 11px;">Hak Investor (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" name="persen_bagian_investor" id="wizard_persen_bagian_investor" class="form-control rounded-start-3 fw-bold" placeholder="50.00" value="50.00" required oninput="balanceOutletSplit('investor')">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary fw-bold px-2 border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden rounded-end-3 bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 22px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepInvestor(1)" style="font-size: 8px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fa-solid fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepInvestor(-1)" style="font-size: 8px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text text-body-secondary mt-1" style="font-size: 9.5px;">Porsi bagi hasil investor.</div>
                            </div>
                            <div class="col-4">
                                <label class="form-label required" style="font-size: 11px;">Hak Outlet (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" name="persen_bagian_outlet" id="wizard_persen_bagian_outlet" class="form-control rounded-start-3 fw-bold" placeholder="50.00" value="50.00" required oninput="balanceOutletSplit('outlet')">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary fw-bold px-2 border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden rounded-end-3 bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 22px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepOutlet(1)" style="font-size: 8px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fa-solid fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepOutlet(-1)" style="font-size: 8px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text text-body-secondary mt-1" style="font-size: 9.5px;">Porsi bagi hasil outlet.</div>
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 440px;">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0 pt-2 px-2.5">
                <h6 class="modal-title fw-bold text-body-emphasis" id="modalEditOutletLabel" style="font-size: 13px;">
                    <i class="fa-light fa-pen-to-square me-1.5 text-warning"></i>Edit Data Outlet
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 8.5px;"></button>
            </div>
            <form id="formEditOutlet" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_outlet" id="edit_id_outlet" value="">
                <div class="modal-body p-2.5" style="max-height: 80vh; overflow-y: auto;">
                    <!-- Sesi 1: Informasi Outlet -->
                    <div class="fw-bold text-danger text-uppercase mb-1" style="font-size: 8px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-store me-1"></i> Informasi Outlet Toko
                    </div>
                    <div class="row g-1.5 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 10px;">Nama Outlet</label>
                            <input type="text" name="nama_outlet" id="edit_nama_outlet" class="form-control form-control-sm rounded-3 fw-semibold py-1 px-2" style="font-size: 10.5px; height: 28px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 10px;">Kecamatan</label>
                            <input type="text" name="kecamatan" id="edit_kecamatan" class="form-control form-control-sm rounded-3 fw-semibold py-1 px-2" style="font-size: 10.5px; height: 28px;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 10px;">Alamat Lengkap</label>
                            <textarea name="alamat_outlet" id="edit_alamat_outlet" class="form-control form-control-sm rounded-3 fw-semibold py-1 px-2" rows="1" style="font-size: 10.5px; min-height: 32px;" required></textarea>
                        </div>
                    </div>

<style>
.custom-toggle-switch {
    position: relative;
    display: inline-block;
    width: 38px;
    height: 20px;
    flex-shrink: 0;
    vertical-align: middle;
}
.custom-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.custom-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .2s ease-in-out;
    border-radius: 20px;
    border: 1.5px solid #94a3b8;
}
.custom-toggle-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 2px;
    bottom: 1.5px;
    background-color: #ffffff;
    transition: .2s ease-in-out;
    border-radius: 50%;
    box-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
.custom-toggle-switch input:checked + .custom-toggle-slider {
    background-color: #dc3545 !important;
    border-color: #b02a37 !important;
}
.custom-toggle-switch input:checked + .custom-toggle-slider:before {
    transform: translateX(17px);
    background-color: #ffffff !important;
}
</style>

                    <!-- Sesi 2: Skema Potongan & Bagi Hasil -->
                    <div class="fw-bold text-danger text-uppercase mt-2 mb-1" style="font-size: 8px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-chart-pie me-1"></i> Skema Potongan & Bagi Hasil
                    </div>
                    <div class="row g-1.5 mb-2">
                        <!-- Potongan Omzet -->
                        <div class="col-4">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 9.5px;">Potongan Omzet</label>
                            <div class="input-group input-group-sm" style="height: 28px;">
                                <input type="number" step="0.5" min="0" max="100" name="persentase_potongan" id="edit_persentase_potongan" class="form-control form-control-sm rounded-start-3 fw-bold text-center px-1 py-0" style="font-size: 10.5px; height: 28px;" required>
                                <span class="input-group-text bg-body-tertiary text-body-secondary fw-bold px-1 py-0 border-end-0" style="font-size: 9px; height: 28px;">%</span>
                                <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary rounded-end-3" style="height: 28px;">
                                    <div class="d-flex flex-column h-100" style="width: 20px;">
                                        <button type="button" class="btn btn-sm btn-light border-0 rounded-0 p-0 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepEditPotongan(1)" style="font-size: 7.5px; line-height: 1;" title="Tambah (+1%)">
                                            <i class="fa-solid fa-chevron-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 p-0 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepEditPotongan(-1)" style="font-size: 7.5px; line-height: 1;" title="Kurangi (-1%)">
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hak Investor -->
                        <div class="col-4">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 9.5px;">Hak Investor</label>
                            <div class="input-group input-group-sm" style="height: 28px;">
                                <input type="number" step="0.5" min="0" max="100" name="persen_bagian_investor" id="edit_persen_bagian_investor" class="form-control form-control-sm rounded-start-3 fw-bold text-center px-1 py-0" style="font-size: 10.5px; height: 28px;" required oninput="balanceEditOutletSplit('investor')">
                                <span class="input-group-text bg-body-tertiary text-body-secondary fw-bold px-1 py-0 border-end-0" style="font-size: 9px; height: 28px;">%</span>
                                <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary rounded-end-3" style="height: 28px;">
                                    <div class="d-flex flex-column h-100" style="width: 20px;">
                                        <button type="button" class="btn btn-sm btn-light border-0 rounded-0 p-0 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepEditInvestor(1)" style="font-size: 7.5px; line-height: 1;" title="Tambah (+1%)">
                                            <i class="fa-solid fa-chevron-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 p-0 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepEditInvestor(-1)" style="font-size: 7.5px; line-height: 1;" title="Kurangi (-1%)">
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hak Outlet -->
                        <div class="col-4">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 9.5px;">Hak Outlet</label>
                            <div class="input-group input-group-sm" style="height: 28px;">
                                <input type="number" step="0.5" min="0" max="100" name="persen_bagian_outlet" id="edit_persen_bagian_outlet" class="form-control form-control-sm rounded-start-3 fw-bold text-center px-1 py-0" style="font-size: 10.5px; height: 28px;" required oninput="balanceEditOutletSplit('outlet')">
                                <span class="input-group-text bg-body-tertiary text-body-secondary fw-bold px-1 py-0 border-end-0" style="font-size: 9px; height: 28px;">%</span>
                                <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary rounded-end-3" style="height: 28px;">
                                    <div class="d-flex flex-column h-100" style="width: 20px;">
                                        <button type="button" class="btn btn-sm btn-light border-0 rounded-0 p-0 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepEditOutlet(1)" style="font-size: 7.5px; line-height: 1;" title="Tambah (+1%)">
                                            <i class="fa-solid fa-chevron-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 p-0 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepEditOutlet(-1)" style="font-size: 7.5px; line-height: 1;" title="Kurangi (-1%)">
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sesi 2.5: Rentang Tanggal Penyesuaian Skema (Opsional) -->
                    <div class="p-2.5 mb-2 rounded-3 bg-body-tertiary border border-body-subtle">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <label class="custom-toggle-switch flex-shrink-0 mb-0 me-1">
                                <input type="checkbox" id="chk_apply_date_range" name="apply_date_range" value="1">
                                <span class="custom-toggle-slider"></span>
                            </label>
                            <label class="form-check-label fw-bold text-body-emphasis mb-0 cursor-pointer" for="chk_apply_date_range" style="font-size: 10px; line-height: 1.3;">
                                <i class="fa-solid fa-calendar-range me-1 text-danger"></i> Terapkan Skema pada Rentang Tanggal Spesifik
                                <span id="lbl_date_range_status" class="badge bg-danger text-white ms-1.5 d-none" style="font-size: 8px; padding: 2px 5px; border-radius: 4px;">Aktif</span>
                            </label>
                        </div>
                        
                        <div id="container_edit_date_range" class="row g-2 mt-2 pt-2 border-top border-body-subtle d-none">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-body-secondary mb-1 d-block" style="font-size: 9.5px; margin-bottom: 4px;">Tanggal Mulai</label>
                                <div class="input-group input-group-sm date-picker-wrapper cursor-pointer" style="height: 28px;">
                                    <span class="input-group-text bg-body border-body-subtle text-danger py-0 px-2" style="height: 28px;"><i class="fa-solid fa-calendar-days" style="font-size: 10px;"></i></span>
                                    <input type="date" name="tgl_mulai_skema" id="edit_tgl_mulai_skema" class="form-control form-control-sm bg-body fw-bold py-0 px-1.5 text-body-emphasis" style="font-size: 10.5px; height: 28px;" onclick="if(this.showPicker){this.showPicker();}">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-body-secondary mb-1 d-block" style="font-size: 9.5px; margin-bottom: 4px;">Tanggal Selesai</label>
                                <div class="input-group input-group-sm date-picker-wrapper cursor-pointer" style="height: 28px;">
                                    <span class="input-group-text bg-body border-body-subtle text-danger py-0 px-2" style="height: 28px;"><i class="fa-solid fa-calendar-days" style="font-size: 10px;"></i></span>
                                    <input type="date" name="tgl_selesai_skema" id="edit_tgl_selesai_skema" class="form-control form-control-sm bg-body fw-bold py-0 px-1.5 text-body-emphasis" style="font-size: 10.5px; height: 28px;" onclick="if(this.showPicker){this.showPicker();}">
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="form-text text-body-secondary mb-0 lh-sm" style="font-size: 9px;">
                                    <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                                    Skema potongan dan bagi hasil di atas akan diterapkan khusus untuk laporan omzet harian pada rentang tanggal yang ditentukan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sesi 3: Akun Pengelola / Kasir -->
                    <div class="fw-bold text-danger text-uppercase mt-2 mb-1" style="font-size: 8px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-user-gear me-1"></i> Akun Pengelola / Kasir
                    </div>
                    <div class="row g-1.5 mb-1">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 10px;">Nama Kasir</label>
                            <input type="text" name="nama_pengelola" id="edit_nama_pengelola" class="form-control form-control-sm rounded-3 fw-semibold py-1 px-2" style="font-size: 10.5px; height: 28px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 10px;">No. HP / WhatsApp</label>
                            <input type="text" name="no_hp" id="edit_no_hp" class="form-control form-control-sm rounded-3 fw-semibold py-1 px-2" style="font-size: 10.5px; height: 28px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-body-secondary required mb-0.5" style="font-size: 10px;">Username Login</label>
                            <div class="input-group input-group-sm" style="height: 28px;">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary px-1.5 py-0" style="font-size: 9.5px; height: 28px;">@</span>
                                <input type="text" name="username" id="edit_username" class="form-control form-control-sm rounded-end-3 border-start-0 fw-semibold py-1 px-2" style="font-size: 10.5px; height: 28px;" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-body-secondary mb-0.5" style="font-size: 10px;">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control form-control-sm rounded-3 fw-semibold py-1 px-2" style="font-size: 10.5px; height: 28px;" placeholder="Kosongkan jika tetap">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-1 pb-2 px-2.5 d-flex justify-content-end gap-1.5">
                    <button type="button" class="btn btn-light rounded-pill px-3 py-1 btn-sm" data-bs-dismiss="modal" style="font-size: 10.5px;">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-3 py-1 btn-sm" style="font-size: 10.5px;">
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

                    <!-- Container Banner Action (Opsi B: Perpanjang / Ajukan Ulang / Notifikasi Verifikasi) -->
                    <div id="det_banner_action"></div>

                    <div class="list-group list-group-flush rounded-3 border border-body-subtle mb-3">
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
<!-- MODAL: PERPANJANG LANGGANAN OUTLET (Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalPerpanjangOutlet" tabindex="-1" aria-labelledby="modalPerpanjangOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <h6 class="modal-title fw-bold text-body-emphasis" id="modalPerpanjangOutletLabel">
                    <i class="fa-solid fa-rotate-right me-1.5 text-danger"></i>Perpanjang Langganan Outlet
                </h6>
                <button type="button" class="btn-close" style="font-size: 0.8rem;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPerpanjangOutlet" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="renew_action" value="request_perpanjangan">
                <input type="hidden" name="id_outlet" id="renew_id_outlet" value="">
                <div class="modal-body p-3">
                    <!-- Section Alasan Penolakan (khusus Ajukan Ulang) -->
                    <div id="renew_alasan_container" class="d-none">
                        <div class="alert alert-danger bg-danger-subtle border-0 rounded-3 p-2 mb-2">
                            <div class="d-flex align-items-center gap-2 mb-1 text-danger" style="font-size: 13px;">
                                <i class="fa-solid fa-circle-xmark fs-6"></i>
                                <strong class="fw-bold">Alasan Penolakan Admin:</strong>
                            </div>
                            <div class="p-2 bg-white text-dark rounded-3 border border-danger-subtle fw-semibold mt-2" id="renew_alasan_text" style="font-size: 12px; line-height: 1.5; word-break: break-word;">
                                -
                            </div>
                        </div>
                        <div class="text-center my-2 text-body-emphasis fw-bold" style="font-size: 11.5px;">
                            Silahkan isi form di bawah ini untuk mengajukan ulang
                        </div>
                    </div>

                    <div class="alert alert-danger bg-danger-subtle border-0 rounded-3 p-2 mb-2">
                        <div class="d-flex align-items-center gap-2 mb-1" style="font-size: 13px;">
                            <i class="fa-solid fa-store text-danger fs-6"></i>
                            <strong class="text-body-emphasis fw-bold" id="renew_nama_outlet">-</strong>
                        </div>
                        <p class="small text-body-secondary mb-0" style="font-size: 11.5px;">Biaya Langganan: <strong class="text-danger">Rp <?= number_format($biayaLangganan, 0, ',', '.'); ?></strong> / bln</p>
                    </div>

                    <!-- Informasi Rekening Bank Admin -->
                    <div class="border border-body-subtle bg-body-tertiary rounded-3 p-2 mb-2">
                        <small class="text-body-secondary d-block fw-semibold mb-1" style="font-size: 11px;"><i class="fa-solid fa-building-columns me-1 text-primary"></i> Rekening Transfer:</small>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold text-body-emphasis d-block" style="font-size: 13px;"><?= htmlspecialchars($bankNama); ?> - <?= htmlspecialchars($bankNoRek); ?></span>
                                <small class="text-body-secondary" style="font-size: 11px;">a.n. <?= htmlspecialchars($bankAtasNama); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-body-secondary required" style="font-size: 11.5px; margin-bottom: 4px;">Upload Bukti Transfer Pembayaran</label>
                        <input type="file" name="bukti_pembayaran" class="form-control form-control-sm rounded-3" accept="image/*,.pdf" required>
                        <div class="form-text mt-1 text-body-secondary" style="font-size: 10.5px;">Format: JPG, PNG, PDF yang terbaca jelas.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3 px-3">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger text-white fw-bold rounded-pill px-3" id="btnSubmitRenew">
                        <i class="fa-solid fa-paper-plane me-1"></i> Kirim Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ========================================================================= -->
<!-- ========================================================================= -->
<!-- MODAL: FILTER DATA OUTLET (Rentang Tanggal Pendaftaran) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalFilterOutlet" tabindex="-1" aria-labelledby="modalFilterOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg bg-body" style="border-radius: 20px;">
            <div class="modal-header border-bottom border-body-subtle py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="modal-title fw-extrabold text-body-emphasis mb-0 fs-6" id="modalFilterOutletLabel">
                        <i class="fa-solid fa-filter me-2 text-danger"></i>Filter Rentang Tanggal Outlet
                    </h6>
                    <small class="text-body-secondary" style="font-size: 11px;">Pilih tanggal mulai dan tanggal selesai pendaftaran outlet</small>
                </div>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="<?= SystemInfo::app('CLIENT_URL'); ?>/outlet">
                <div class="modal-body p-4">
                    <!-- Header Label & Reset Tanggal Button (Matched with Bagi Hasil Modal) -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-body-secondary mb-0">
                            <i class="fa-regular fa-calendar-range me-1 text-danger"></i>Pilih Rentang Tanggal (Bebas)
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 fw-bold px-2 py-0" id="btnResetTanggalFilterOutlet" style="font-size: 11px;">
                            <i class="fa-solid fa-rotate-left me-1"></i>Reset Tanggal
                        </button>
                    </div>

                    <div class="row g-2">
                        <!-- Tanggal Mulai -->
                        <div class="col-6">
                            <label for="filter_tgl_mulai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Mulai</label>
                            <div class="input-group input-group-sm cursor-pointer date-picker-wrapper">
                                <span class="input-group-text bg-body-tertiary border-body-subtle text-danger"><i class="fa-solid fa-calendar-days"></i></span>
                                <input type="date" name="tgl_mulai" id="filter_tgl_mulai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglMulai); ?>" onclick="if(this.showPicker){this.showPicker();}">
                            </div>
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="col-6">
                            <label for="filter_tgl_selesai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Selesai</label>
                            <div class="input-group input-group-sm cursor-pointer date-picker-wrapper">
                                <span class="input-group-text bg-body-tertiary border-body-subtle text-danger"><i class="fa-solid fa-calendar-days"></i></span>
                                <input type="date" name="tgl_selesai" id="filter_tgl_selesai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglSelesai); ?>" onclick="if(this.showPicker){this.showPicker();}">
                            </div>
                        </div>
                    </div>

                    <div class="form-text text-body-secondary small mt-2">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>*Klik <strong>Reset Tanggal</strong> untuk menghapus filter tanggal dan menampilkan <strong>seluruh data outlet</strong> tanpa batasan periode.
                    </div>
                </div>
                <div class="modal-footer border-top border-body-subtle py-3 px-4 d-flex justify-content-between">
                    <a href="<?= SystemInfo::app('CLIENT_URL'); ?>/outlet" class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 12px;">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset Filter
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3 py-1.5 fw-semibold" data-bs-dismiss="modal" style="font-size: 12px;">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 py-1.5 fw-bold shadow-sm" style="font-size: 12px;">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const ACTION_URL = '<?= SystemInfo::app('CLIENT_URL'); ?>/doc/outlet/action.php';

    // Reset Tanggal Filter Event
    $('#btnResetTanggalFilterOutlet').on('click', function() {
        $('#filter_tgl_mulai').val('');
        $('#filter_tgl_selesai').val('');
    });

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

    window.stepPotongan = function(dir) {
        let el = $('#wizard_persentase_potongan');
        let val = parseFloat(el.val()) || 0;
        val = Math.max(0, Math.min(100, val + dir));
        el.val(val.toFixed(2));
    };

    window.stepInvestor = function(dir) {
        let el = $('#wizard_persen_bagian_investor');
        let val = parseFloat(el.val()) || 0;
        val = Math.max(0, Math.min(100, val + dir));
        el.val(val.toFixed(2));
        window.balanceOutletSplit('investor');
    };

    window.stepOutlet = function(dir) {
        let el = $('#wizard_persen_bagian_outlet');
        let val = parseFloat(el.val()) || 0;
        val = Math.max(0, Math.min(100, val + dir));
        el.val(val.toFixed(2));
        window.balanceOutletSplit('outlet');
    };

    window.balanceOutletSplit = function(source) {
        if (source === 'investor') {
            const invVal = parseFloat($('#wizard_persen_bagian_investor').val());
            if (!isNaN(invVal)) {
                const outVal = Math.max(0, 100 - invVal);
                $('#wizard_persen_bagian_outlet').val(outVal.toFixed(2));
            }
        } else {
            const outVal = parseFloat($('#wizard_persen_bagian_outlet').val());
            if (!isNaN(outVal)) {
                const invVal = Math.max(0, 100 - outVal);
                $('#wizard_persen_bagian_investor').val(invVal.toFixed(2));
            }
        }
    };

    window.balanceEditOutletSplit = function(source) {
        if (source === 'investor') {
            const invVal = parseFloat($('#edit_persen_bagian_investor').val());
            if (!isNaN(invVal)) {
                const outVal = Math.max(0, 100 - invVal);
                $('#edit_persen_bagian_outlet').val(outVal.toFixed(2));
            }
        } else {
            const outVal = parseFloat($('#edit_persen_bagian_outlet').val());
            if (!isNaN(outVal)) {
                const invVal = Math.max(0, 100 - outVal);
                $('#edit_persen_bagian_investor').val(invVal.toFixed(2));
            }
        }
    };

    window.stepEditPotongan = function(dir) {
        let el = $('#edit_persentase_potongan');
        let val = parseFloat(el.val()) || 0;
        val = Math.max(0, Math.min(100, val + dir));
        el.val(val.toFixed(2));
    };

    window.stepEditInvestor = function(dir) {
        let el = $('#edit_persen_bagian_investor');
        let val = parseFloat(el.val()) || 0;
        val = Math.max(0, Math.min(100, val + dir));
        el.val(val.toFixed(2));
        window.balanceEditOutletSplit('investor');
    };

    window.stepEditOutlet = function(dir) {
        let el = $('#edit_persen_bagian_outlet');
        let val = parseFloat(el.val()) || 0;
        val = Math.max(0, Math.min(100, val + dir));
        el.val(val.toFixed(2));
        window.balanceEditOutletSplit('outlet');
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
                    text: 'Harap isi Persentase Potongan Omzet.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_persentase_potongan').focus());
                return;
            }

            const persenInv = $('#wizard_persen_bagian_investor').val().trim();
            if (!persenInv) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi Persentase Hak Investor.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_persen_bagian_investor').focus());
                return;
            }

            const persenOut = $('#wizard_persen_bagian_outlet').val().trim();
            if (!persenOut) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Harap isi Persentase Hak Outlet.',
                    confirmButtonColor: '#7D0A0A'
                }).then(() => $('#wizard_persen_bagian_outlet').focus());
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

    // Toggle custom date range section in Edit Outlet Modal
    $(document).on('change', '#chk_apply_date_range', function() {
        if ($(this).is(':checked')) {
            $('#container_edit_date_range').removeClass('d-none');
            $('#lbl_date_range_status').removeClass('d-none');
        } else {
            $('#container_edit_date_range').addClass('d-none');
            $('#lbl_date_range_status').addClass('d-none');
            $('#edit_tgl_mulai_skema').val('');
            $('#edit_tgl_selesai_skema').val('');
        }
    });

    // 2. Fetch Detail for Edit Outlet
    $(document).on('click', '.btn-edit-outlet', function() {
        const idOutlet = $(this).data('id');

        // Reset custom date range fields
        $('#chk_apply_date_range').prop('checked', false);
        $('#container_edit_date_range').addClass('d-none');
        $('#lbl_date_range_status').addClass('d-none');
        $('#edit_tgl_mulai_skema').val('');
        $('#edit_tgl_selesai_skema').val('');
        
        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_outlet: idOutlet },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#edit_id_outlet').val(res.data.id_outlet);
                    $('#edit_nama_outlet').val(res.data.nama_outlet);
                    $('#edit_kecamatan').val(res.data.kecamatan);
                    $('#edit_alamat_outlet').val(res.data.alamat_outlet);
                    $('#edit_persentase_potongan').val(parseFloat(res.data.persentase_potongan).toFixed(2));
                    
                    const invPct = parseFloat(res.data.persen_bagian_investor) || 50.00;
                    $('#edit_persen_bagian_investor').val(invPct.toFixed(2));
                    $('#edit_persen_bagian_outlet').val((100 - invPct).toFixed(2));

                    $('#edit_nama_pengelola').val(res.data.nama_lengkap);
                    $('#edit_no_hp').val(res.data.no_hp);
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
                        $('#det_bukti_container').html('<button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold btn-preview-image-lightbox" data-src="' + fileUrl + '" data-title="Bukti Pembayaran - ' + res.data.nama_outlet + '"><i class="fa-solid fa-expand me-1.5"></i> Lihat Bukti Bayar</button>');
                    } else {
                        $('#det_bukti_container').text('-');
                    }
                    $('#det_total_omzet').text('Rp ' + new Intl.NumberFormat('id-ID').format(res.data.total_omzet));
                    $('#det_total_laporan').text(res.data.total_laporan + ' Laporan');

                    // Render Opsi B Action Banner inside Modal Detail
                    let statusText = res.data.status || 'active';
                    let tipeReq = res.data.tipe_request || 'baru';
                    let jtRaw = res.data.tgl_jatuh_tempo || '';
                    let today = new Date().toISOString().split('T')[0];
                    let jt = jtRaw ? jtRaw.split(' ')[0] : '';
                    let isExpired = (jt && today > jt);
                    let daysRem = 999;
                    if (jt && !isExpired) {
                        let d1 = new Date(today);
                        let d2 = new Date(jt);
                        daysRem = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
                    }
                    let isNearExp = (jt && !isExpired && daysRem <= 7);

                    let bannerHtml = '';
                    if (statusText === 'reject') {
                        let titleReject = (tipeReq === 'perpanjangan') ? 'Perpanjangan Langganan Ditolak Admin' : 'Pendaftaran Baru Ditolak Admin';
                        let btnReject = (tipeReq === 'perpanjangan') ? 'Ajukan Ulang Perpanjangan' : 'Ajukan Ulang Pendaftaran';
                        bannerHtml = `
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-3 text-center">
                                <i class="fa-solid fa-circle-xmark text-danger fs-4 mb-1"></i>
                                <div class="fw-bold text-danger mb-2">${titleReject}</div>
                                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1 btn-trigger-ajukan-ulang" data-id="${res.data.id_outlet}" data-nama="${res.data.nama_outlet}" data-alasan="${res.data.alasan_penolakan || 'Tidak ada catatan alasan dari admin.'}">
                                    <i class="fa-solid fa-paper-plane me-1"></i> ${btnReject}
                                </button>
                            </div>`;
                    } else if (statusText === 'pending') {
                        let labelReq = (tipeReq === 'perpanjangan') ? 'Perpanjangan' : 'Pendaftaran Baru';
                        bannerHtml = `
                            <div class="alert alert-warning border-0 shadow-sm rounded-3 p-3 mb-3 text-center">
                                <i class="fa-solid fa-clock-rotate-left text-warning fs-4 mb-1"></i>
                                <div class="fw-bold text-body-emphasis mb-1">Permohonan ${labelReq} Sedang Diverifikasi Admin</div>
                                <small class="text-body-secondary">Mohon tunggu konfirmasi verifikasi dari pihak Admin.</small>
                            </div>`;
                    } else if (statusText === 'active' && (isExpired || isNearExp)) {
                        let labelExp = isExpired ? 'Masa Langganan Outlet Telah Berakhir (Expired)' : 'Masa Langganan Mendekati Expired (H-' + daysRem + ')';
                        bannerHtml = `
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-3 text-center">
                                <i class="fa-solid fa-triangle-exclamation text-danger fs-4 mb-1"></i>
                                <div class="fw-bold text-body-emphasis mb-2">${labelExp}</div>
                                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1 btn-trigger-perpanjang" data-id="${res.data.id_outlet}" data-nama="${res.data.nama_outlet}">
                                    <i class="fa-solid fa-rotate-right me-1"></i> Perpanjang Langganan
                                </button>
                            </div>`;
                    }

                    $('#det_banner_action').html(bannerHtml);
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
            title: `<div class="text-danger fw-extrabold fs-5 mb-0"><i class="fa-solid fa-location-dot me-2"></i>Detail Alamat Toko</div>`,
            html: `
                <div class="text-start bg-light p-4 rounded-4 border border-secondary-subtle mt-3 mb-1 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom">
                        <span class="text-secondary small fw-bold text-uppercase d-inline-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-store text-danger me-2 fs-6"></i>Nama Outlet
                        </span>
                        <strong class="text-dark fs-6 ms-2 text-end">${nama}</strong>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom">
                        <span class="text-secondary small fw-bold text-uppercase d-inline-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-map-pin text-danger me-2 fs-6"></i>Kecamatan
                        </span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold ms-2" style="font-size: 12px;">${kec}</span>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-2 text-uppercase d-inline-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-house-chimney text-danger me-2 fs-6"></i>Alamat Lengkap Toko
                        </div>
                        <div class="p-3 bg-white rounded-3 border border-secondary-subtle text-dark fw-semibold" style="font-size: 13.5px; line-height: 1.6; text-align: left; word-break: break-word;">
                            ${alamat}
                        </div>
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
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

    // Handle Click Trigger Perpanjang dari Modal Detail
    $(document).on('click', '.btn-trigger-perpanjang', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        $('#renew_alasan_container').addClass('d-none');
        $('#modalDetailOutlet').modal('hide');
        setTimeout(function() {
            $('#renew_action').val('request_perpanjangan');
            $('#modalPerpanjangOutletLabel').html('<i class="fa-solid fa-rotate-right me-2 text-danger"></i>Perpanjang Langganan Outlet');
            $('#renew_id_outlet').val(id);
            $('#renew_nama_outlet').text(nama);
            $('#btnSubmitRenew').html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Request Perpanjangan');
            $('#modalPerpanjangOutlet').modal('show');
        }, 400);
    });

    // Handle Click Trigger Ajukan Ulang Pembayaran dari Modal Detail
    $(document).on('click', '.btn-trigger-ajukan-ulang', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        let alasan = $(this).data('alasan');
        $('#modalDetailOutlet').modal('hide');
        setTimeout(function() {
            $('#renew_action').val('ajukan_ulang');
            $('#modalPerpanjangOutletLabel').html('<i class="fa-solid fa-paper-plane me-2 text-danger"></i>Ajukan Ulang Pembayaran Outlet');
            $('#renew_id_outlet').val(id);
            $('#renew_nama_outlet').text(nama);
            $('#renew_alasan_text').text(alasan || 'Tidak ada catatan alasan dari admin.');
            $('#renew_alasan_container').removeClass('d-none');
            $('#btnSubmitRenew').html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Pengajuan Ulang');
            $('#modalPerpanjangOutlet').modal('show');
        }, 400);
    });

    // Handle Form Submit Perpanjangan Langganan via AJAX
    $('#formPerpanjangOutlet').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $('#btnSubmitRenew').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Mengirim...');

        $.ajax({
            url: ACTION_URL,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                $('#btnSubmitRenew').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Request Perpanjangan');
                if (res.success) {
                    $('#modalPerpanjangOutlet').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        confirmButtonColor: '#7D0A0A'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: res.message,
                        confirmButtonColor: '#7D0A0A'
                    });
                }
            },
            error: function() {
                $('#btnSubmitRenew').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Request Perpanjangan');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan sistem saat mengirim request perpanjangan.',
                    confirmButtonColor: '#7D0A0A'
                });
            }
        });
    });

    // 6. Cek Alasan Penolakan Outlet & Ajukan Ulang
    $(document).on('click', '.btn-cek-alasan', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const alasan = $(this).data('alasan');

        $('#renew_action').val('ajukan_ulang');
        $('#modalPerpanjangOutletLabel').html('<i class="fa-solid fa-paper-plane me-2 text-danger"></i>Ajukan Ulang Pembayaran Outlet');
        $('#renew_id_outlet').val(id);
        $('#renew_nama_outlet').text(nama);
        $('#renew_alasan_text').text(alasan || 'Tidak ada catatan alasan dari admin.');
        $('#renew_alasan_container').removeClass('d-none');
        $('#btnSubmitRenew').html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Pengajuan Ulang');
        $('#modalPerpanjangOutlet').modal('show');
    });

    // 7. Detail Pengajuan Pending Outlet (SweetAlert2)
    $(document).on('click', '.btn-detail-pending', function() {
        const nama  = $(this).data('nama');
        const tipe  = $(this).data('tipe');
        const waktu = $(this).data('waktu');
        const bukti = $(this).data('bukti');

        let buktiContent = '';
        if (bukti) {
            let isImg = (bukti.match(/\.(jpeg|jpg|gif|png|webp)$/i));
            if (isImg) {
                buktiContent = `
                    <div class="mt-2 text-center">
                        <div class="d-inline-block btn-preview-image-lightbox" data-src="${bukti}" data-title="Bukti Pembayaran - ${nama}" style="cursor: pointer;">
                            <img src="${bukti}" class="img-fluid rounded-3 border border-secondary-subtle shadow-xs mb-1" style="max-height: 180px; object-fit: contain;" title="Klik untuk perbesar gambar">
                            <div class="small text-danger fw-bold mt-1" style="font-size: 11px;">
                                <i class="fa-solid fa-expand me-1"></i>Klik gambar untuk memperbesar
                            </div>
                        </div>
                    </div>
                `;
            } else {
                buktiContent = `
                    <div class="mt-2 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3.5 py-1.5 fw-bold btn-preview-image-lightbox" data-src="${bukti}" data-title="Dokumen Bukti Pembayaran - ${nama}" style="font-size: 12px;">
                            <i class="fa-solid fa-file-arrow-down me-1.5"></i>Lihat Dokumen Bukti Pembayaran
                        </button>
                    </div>
                `;
            }
        } else {
            buktiContent = `
                <div class="p-3 bg-white rounded-3 border border-secondary-subtle text-secondary fw-semibold text-center" style="font-size: 12.5px;">
                    Belum ada lampiran bukti pembayaran.
                </div>
            `;
        }

        Swal.fire({
            title: '<div class="text-danger fw-extrabold fs-5 mb-0"><i class="fa-solid fa-circle-info me-2"></i>Detail Pengajuan Outlet</div>',
            html: `
                <div class="text-start p-4 bg-light rounded-4 border border-secondary-subtle mt-3 mb-1 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom">
                        <span class="text-secondary small fw-bold text-uppercase d-inline-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-store text-danger me-2 fs-6"></i>Nama Toko
                        </span>
                        <span class="fw-bold text-dark fs-6 ms-2 text-end">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom">
                        <span class="text-secondary small fw-bold text-uppercase d-inline-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-tag text-danger me-2 fs-6"></i>Jenis Pengajuan
                        </span>
                        <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-1.5 rounded-pill fw-bold ms-2" style="font-size: 11px;">${tipe}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom">
                        <span class="text-secondary small fw-bold text-uppercase d-inline-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-regular fa-clock text-danger me-2 fs-6"></i>Waktu Pendaftaran
                        </span>
                        <span class="fw-bold text-dark font-monospace small ms-2 text-end">${waktu}</span>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-2 text-uppercase d-inline-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-receipt text-danger me-2 fs-6"></i>Bukti Pembayaran
                        </div>
                        ${buktiContent}
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });

    // 8. Global In-Page Lightbox Image Preview with Top-Right Close (X) Button
    $(document).on('click', '.btn-preview-image-lightbox', function(e) {
        e.preventDefault();
        const imgUrl = $(this).data('src');
        const titleText = $(this).data('title') || 'Bukti Pembayaran';

        if (!imgUrl) return;

        Swal.fire({
            html: `
                <div class="position-relative w-100 text-center" style="background: #1a1a1a; border-radius: 16px; overflow: hidden;">
                    <!-- Tombol X di pojok kanan atas -->
                    <button onclick="Swal.close();"
                        style="position: absolute; top: 10px; right: 10px; z-index: 9999;
                               width: 36px; height: 36px; border-radius: 50%;
                               background: #dc3545; border: 2px solid #fff;
                               color: #fff; font-size: 18px; font-weight: bold;
                               line-height: 1; cursor: pointer;
                               display: flex; align-items: center; justify-content: center;
                               box-shadow: 0 2px 8px rgba(0,0,0,0.5);"
                        title="Tutup">&times;</button>

                    <div class="px-3 pt-3 pb-1 text-truncate" style="color: #aaa; font-size: 12px; font-weight: 600; letter-spacing: 0.4px; padding-right: 50px !important;">
                        <i class="fa-solid fa-image me-1" style="color: #ffc107;"></i>${titleText}
                    </div>

                    <div style="padding: 8px 12px; max-height: 78vh; display: flex; align-items: center; justify-content: center;">
                        <img src="${imgUrl}" style="max-height: 75vh; max-width: 100%; object-fit: contain; border-radius: 8px;" alt="Bukti Pembayaran">
                    </div>

                    <div style="padding: 8px 12px 12px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #333;">
                        <span style="font-size: 11px; color: #888;"><i class="fa-solid fa-circle-info me-1" style="color: #ffc107;"></i>Tekan <b style='color:#ccc'>ESC</b> atau tombol <b style='color:#ccc'>✕</b> untuk keluar</span>
                        <a href="${imgUrl}" download style="font-size: 11px; color: #ccc; text-decoration: none; padding: 3px 10px; border: 1px solid #555; border-radius: 20px;">
                            <i class="fa-solid fa-download me-1"></i>Unduh
                        </a>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: false,
            background: 'transparent',
            width: '90%',
            padding: 0,
            customClass: {
                popup: 'p-0 bg-transparent border-0 shadow-none'
            }
        });
    });
});
</script>
