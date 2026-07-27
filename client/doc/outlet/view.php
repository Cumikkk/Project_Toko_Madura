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

// Get Investor ID for logged-in user
$resInv = $db->query("SELECT id_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
$investorId = 0;
if ($resInv && $resInv->num_rows > 0) {
    $investorId = (int)$resInv->fetch_assoc()['id_investor'];
} else {
    $db->query("INSERT INTO investor (id_users, id_master, alamat_investor, persen_bagian_investor) VALUES ({$userId}, 1, 'Bangkalan', 50.00)");
    $investorId = $db->insert_id;
}

// Separate Month & Year Filter (Default: 0 = Semua Bulan)
$selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$availableYears = [];
// Fetch distinct years available in database for this investor's outlets
$resYears = $db->query("SELECT DISTINCT YEAR(l.periode_laporan) as y_periode FROM laporan_omzet l JOIN outlet o ON l.id_outlet = o.id_outlet WHERE o.id_investor = {$investorId} ORDER BY y_periode DESC");
if ($resYears) {
    while ($yRow = $resYears->fetch_assoc()) {
        $availableYears[] = (int)$yRow['y_periode'];
    }
}
if (!in_array((int)date('Y'), $availableYears)) {
    array_unshift($availableYears, (int)date('Y'));
}

// Build WHERE clause for filtering laporan_omzet
$whereOmzet = [];
if ($selectedBulan > 0) {
    $whereOmzet[] = "MONTH(l.periode_laporan) = {$selectedBulan}";
}
if ($selectedTahun > 0) {
    $whereOmzet[] = "YEAR(l.periode_laporan) = {$selectedTahun}";
}
$whereOmzetSql = !empty($whereOmzet) ? " AND " . implode(" AND ", $whereOmzet) : "";

// Fetch Outlets belonging to this Investor with omzet filter
$sqlOutlets = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        o.alamat_outlet,
        o.id_users,
        u.username,
        IFNULL(SUM(l.omzet), 0) as total_omzet,
        COUNT(l.id_laporan) as total_laporan
    FROM outlet o
    JOIN users u ON o.id_users = u.id_users
    LEFT JOIN laporan_omzet l ON o.id_outlet = l.id_outlet {$whereOmzetSql}
    WHERE o.id_investor = {$investorId}
    GROUP BY o.id_outlet
    ORDER BY o.id_outlet DESC
";
$resOutlets = $db->query($sqlOutlets);
$outlets = [];
$totalAkumulasiOmzet = 0;

if ($resOutlets) {
    while ($row = $resOutlets->fetch_assoc()) {
        $outlets[] = $row;
        $totalAkumulasiOmzet += (float)$row['total_omzet'];
    }
}
$totalOutlet = count($outlets);

$periodeLabelStr = ($selectedBulan > 0 ? ($bulanIndo[$selectedBulan] ?? '') . ' ' : '') . ($selectedTahun > 0 ? $selectedTahun : '');
if ($selectedBulan === 0 && $selectedTahun === 0) {
    $periodeLabelStr = 'Semua Periode';
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

@media (max-width: 575.98px) {
    .filter-pill-container {
        width: 100%;
        justify-content: space-between;
    }
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

    <!-- Summary Metrics Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                    <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: linear-gradient(135deg, #7D0A0A 0%, #580608 100%);">
                        <i class="fa-light fa-store fs-4"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-body-secondary small fw-semibold">Total Outlet Milik Anda</div>
                        <div class="fs-5 fs-md-4 fw-bold text-body-emphasis mb-0"><?= number_format($totalOutlet, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Outlet</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                    <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-light fa-money-bill-trend-up fs-4"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-body-secondary small fw-semibold">Total Omzet (<?= htmlspecialchars($periodeLabelStr); ?>)</div>
                        <div class="fs-6 fs-md-5 fw-bold text-success mb-0">Rp <?= number_format($totalAkumulasiOmzet, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                <!-- Header with Title & Filter Omzet Periode (Mobile Responsive Pill) -->
                <div class="card-header bg-body py-3 px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between border-bottom border-body-subtle gap-3">
                    <div>
                        <h5 class="fw-bold text-body-emphasis mb-1 fs-6"><i class="fa-solid fa-store me-2 text-danger"></i>Daftar Outlet Terdaftar</h5>
                        <p class="text-body-secondary small mb-0">Rincian omzet bulanan outlet periode <?= htmlspecialchars($periodeLabelStr); ?></p>
                    </div>

                    <!-- Sleek Filter Omzet Periode Bar (Filter Bulan & Tahun Terpisah) -->
                    <div class="filter-pill-container d-inline-flex align-items-center gap-2">
                        <span class="text-body-secondary small fw-semibold text-nowrap"><i class="fa-light fa-filter me-1 text-danger"></i>Filter Omzet:</span>
                        <div class="d-inline-flex align-items-center gap-1">
                            <select id="filterBulanOutlet" title="Pilih Bulan">
                                <option value="0" <?= ($selectedBulan === 0) ? 'selected' : ''; ?>>Semua Bulan</option>
                                <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                    <option value="<?= $mNum; ?>" <?= ($selectedBulan === $mNum) ? 'selected' : ''; ?>>
                                        <?= $mName; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-body-secondary opacity-50">/</span>
                            <select id="filterTahunOutlet" title="Pilih Tahun">
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

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableDataOutlet">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>Nama Outlet</th>
                                    <th>Username Akun</th>
                                    <th>Alamat Outlet</th>
                                    <th>Total Omzet (<?= htmlspecialchars($periodeLabelStr); ?>)</th>
                                    <th>Status</th>
                                    <th class="text-center pe-3" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (!empty($outlets)) : ?>
                                    <?php foreach ($outlets as $index => $row) : ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-body-secondary"><?= $index + 1; ?></td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($row['nama_outlet']); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-body-secondary fw-semibold">
                                                    <i class="fa-light fa-user me-1"></i>@<?= htmlspecialchars($row['username']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-body-secondary"><?= htmlspecialchars($row['alamat_outlet'] ?: '-'); ?></small>
                                            </td>
                                            <td>
                                                <span class="fw-extrabold text-success fs-6">
                                                    Rp <?= number_format((float)$row['total_omzet'], 0, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold">
                                                    <i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i>Aktif
                                                </span>
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
                                                <h5 class="fw-bold text-body-secondary mb-1">Belum Ada Outlet</h5>
                                                <p class="text-body-secondary small mb-3">Klik tombol di bawah untuk mendaftarkan akun outlet baru Anda.</p>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH OUTLET (Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTambahOutlet" tabindex="-1" aria-labelledby="modalTambahOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalTambahOutletLabel">
                    <i class="fa-solid fa-store me-2 text-danger"></i>Mendaftarkan Outlet Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahOutlet" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Nama Outlet</label>
                        <input type="text" name="nama_outlet" class="form-control rounded-3" placeholder="Contoh: Toko Madura Sidoarjo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary">Alamat Outlet</label>
                        <textarea name="alamat_outlet" class="form-control rounded-3" rows="2" placeholder="Contoh: Jl. Raya Taman No. 12, Sidoarjo"></textarea>
                    </div>
                    <hr class="my-3 text-body-secondary opacity-25">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Username Login Outlet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary">@</span>
                            <input type="text" name="username" class="form-control rounded-end-3 border-start-0" placeholder="outlet_sidoarjo" required>
                        </div>
                        <div class="form-text small text-body-secondary">Username ini akan digunakan oleh pengelola toko untuk login ke aplikasi.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Password Login Outlet</label>
                        <input type="password" name="password" class="form-control rounded-3" placeholder="Masukkan password akun" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="fa-solid fa-check me-1"></i> Simpan Outlet
                    </button>
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
                    </div>

                    <div class="list-group list-group-flush rounded-3 border border-body-subtle mb-3">
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-user me-2 text-danger"></i>Username Akun Login</span>
                            <span class="fw-bold text-body-emphasis" id="det_username">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-location-dot me-2 text-danger"></i>Alamat Outlet</span>
                            <span class="fw-semibold text-body-emphasis text-end" id="det_alamat">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-money-bill-trend-up me-2 text-success"></i>Total Omzet Terinput</span>
                            <span class="fw-bold text-success fs-6" id="det_total_omzet">Rp 0</span>
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

<script>
$(document).ready(function() {
    const ACTION_URL = '<?= SystemInfo::app('CLIENT_URL'); ?>/doc/outlet/action.php';

    // Filter Bulan & Filter Tahun Dropdown Change Handlers
    function applyOutletFilters() {
        const bVal = $('#filterBulanOutlet').val();
        const yVal = $('#filterTahunOutlet').val();
        window.location.href = '<?= SystemInfo::app('CLIENT_URL'); ?>/outlet?bulan=' + bVal + '&tahun=' + yVal;
    }

    $('#filterBulanOutlet, #filterTahunOutlet').on('change', function() {
        applyOutletFilters();
    });

    // 1. Submit Form Tambah Outlet
    $('#formTambahOutlet').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: ACTION_URL,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Simpan Outlet');
                if (res.success) {
                    $('#modalTambahOutlet').modal('hide');
                    form[0].reset();
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
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Simpan Outlet');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan outlet.', 'error');
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
                    $('#det_nama_outlet').text(res.data.nama_outlet);
                    $('#det_username').text('@' + res.data.username);
                    $('#det_alamat').text(res.data.alamat_outlet || '-');
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
});
</script>
