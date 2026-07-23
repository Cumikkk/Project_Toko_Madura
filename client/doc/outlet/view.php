<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? 0);

// Get Investor ID for logged-in user
$resInv = $db->query("SELECT id_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
$investorId = 0;
if ($resInv && $resInv->num_rows > 0) {
    $investorId = (int)$resInv->fetch_assoc()['id_investor'];
} else {
    $db->query("INSERT INTO investor (id_users, id_master, alamat_investor, persen_bagian_investor) VALUES ({$userId}, 1, 'Bangkalan', 50.00)");
    $investorId = $db->insert_id;
}

// Fetch Outlets belonging to this Investor
$sqlOutlets = "
    SELECT 
        o.id_outlet,
        o.kode_outlet,
        o.nama_outlet,
        o.alamat_outlet,
        o.id_users,
        u.username,
        IFNULL(SUM(l.omzet), 0) as total_omzet,
        COUNT(l.id_laporan) as total_laporan
    FROM outlet o
    JOIN users u ON o.id_users = u.id_users
    LEFT JOIN laporan_omzet l ON o.id_outlet = l.id_outlet
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
?>

<div class="main-content-inner py-4">
    <!-- Header Banner Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8 col-md-7">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-user-shield me-1"></i> Investor Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white">Data Outlet Sub-Investor</h2>
                            <p class="text-white-50 fs-6 mb-0">Kelola daftar outlet di bawah kepemilikan Anda, daftarkan akun outlet baru, dan lihat rincian total omzet.</p>
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
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background: linear-gradient(135deg, #7D0A0A 0%, #580608 100%);">
                        <i class="fa-light fa-store fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Outlet Milik Anda</div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($totalOutlet, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-muted">Outlet</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-light fa-money-bill-trend-up fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Akumulasi Omzet</div>
                        <div class="fs-4 fw-bold text-success">Rp <?= number_format($totalAkumulasiOmzet, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <i class="fa-light fa-user-check fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Status Pengelolaan</div>
                        <div class="fs-6 fw-bold text-primary">Terverifikasi & Aktif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="fa-light fa-list me-2 text-danger"></i>Daftar Outlet Terdaftar</h5>
                        <p class="text-muted small mb-0">Menampilkan seluruh outlet di bawah akun investor Anda</p>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableDataOutlet">
                            <thead class="table-light">
                                <tr class="text-uppercase small text-muted">
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>Kode Outlet</th>
                                    <th>Nama Outlet</th>
                                    <th>Username Akun</th>
                                    <th>Alamat Outlet</th>
                                    <th>Total Omzet</th>
                                    <th>Status</th>
                                    <th class="text-center pe-3" style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($outlets)) : ?>
                                    <?php foreach ($outlets as $index => $row) : ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-muted"><?= $index + 1; ?></td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-3 fw-bold" style="font-size: 12px;">
                                                    <i class="fa-light fa-tag me-1"></i><?= htmlspecialchars($row['kode_outlet']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_outlet']); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-body-secondary fw-semibold">
                                                    <i class="fa-light fa-user me-1"></i>@<?= htmlspecialchars($row['username']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($row['alamat_outlet'] ?: '-'); ?></small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
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
                                                <i class="fa-light fa-store-slash text-muted mb-3" style="font-size: 60px; opacity: 0.5;"></i>
                                                <h5 class="fw-bold text-muted mb-1">Belum Ada Outlet</h5>
                                                <p class="text-muted small mb-3">Klik tombol di bawah untuk mendaftarkan akun outlet baru Anda.</p>
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
<!-- MODAL: TAMBAH OUTLET -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTambahOutlet" tabindex="-1" aria-labelledby="modalTambahOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="modalTambahOutletLabel">
                    <i class="fa-solid fa-store me-2 text-danger"></i>Mendaftarkan Outlet Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahOutlet" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted required">Nama Outlet</label>
                        <input type="text" name="nama_outlet" class="form-control rounded-3" placeholder="Contoh: Toko Madura Sidoarjo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Alamat Outlet</label>
                        <textarea name="alamat_outlet" class="form-control rounded-3" rows="2" placeholder="Contoh: Jl. Raya Taman No. 12, Sidoarjo"></textarea>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted required">Username Login Outlet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">@</span>
                            <input type="text" name="username" class="form-control rounded-end-3 border-start-0" placeholder="outlet_sidoarjo" required>
                        </div>
                        <div class="form-text small">Username ini akan digunakan oleh pengelola toko untuk login ke aplikasi.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted required">Password Login Outlet</label>
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
<!-- MODAL: EDIT OUTLET -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditOutlet" tabindex="-1" aria-labelledby="modalEditOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="modalEditOutletLabel">
                    <i class="fa-light fa-pen-to-square me-2 text-warning"></i>Edit Data Outlet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditOutlet" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_outlet" id="edit_id_outlet" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted required">Nama Outlet</label>
                        <input type="text" name="nama_outlet" id="edit_nama_outlet" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Alamat Outlet</label>
                        <textarea name="alamat_outlet" id="edit_alamat_outlet" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted required">Username Login Outlet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">@</span>
                            <input type="text" name="username" id="edit_username" class="form-control rounded-end-3 border-start-0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Password Baru (Opsional)</label>
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
<!-- MODAL: DETAIL OUTLET -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDetailOutlet" tabindex="-1" aria-labelledby="modalDetailOutletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="modalDetailOutletLabel">
                    <i class="fa-light fa-store me-2 text-info"></i>Detail Informasi Outlet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="detailLoading" class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="detailContent" class="d-none">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger p-3 mb-2" style="width: 64px; height: 64px;">
                            <i class="fa-light fa-shop fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1 text-dark" id="detail_nama_outlet">-</h4>
                        <span class="badge bg-danger px-3 py-1 rounded-pill" id="detail_kode_outlet">OUT000</span>
                    </div>

                    <div class="list-group list-group-flush rounded-3 border mb-3">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small"><i class="fa-light fa-user me-2 text-muted"></i>Username Login</span>
                            <span class="fw-bold text-dark" id="detail_username">@username</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small"><i class="fa-light fa-location-dot me-2 text-muted"></i>Alamat Outlet</span>
                            <span class="fw-semibold text-dark text-end" id="detail_alamat">-</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small"><i class="fa-light fa-money-bill-trend-up me-2 text-muted"></i>Total Omzet Terlaporkan</span>
                            <span class="fw-bold text-success fs-6" id="detail_total_omzet">Rp 0</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small"><i class="fa-light fa-file-lines me-2 text-muted"></i>Jumlah Laporan Omzet</span>
                            <span class="fw-bold text-dark" id="detail_total_laporan">0 Laporan</span>
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

    // 1. Submit Tambah Outlet
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
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Simpan Outlet');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan data.', 'error');
            }
        });
    });

    // 2. Fetch Detail for Edit
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

    // 4. Fetch Detail for View
    $(document).on('click', '.btn-detail-outlet', function() {
        const idOutlet = $(this).data('id');
        $('#detailLoading').removeClass('d-none');
        $('#detailContent').addClass('d-none');
        $('#modalDetailOutlet').modal('show');

        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_outlet: idOutlet },
            dataType: 'json',
            success: function(res) {
                $('#detailLoading').addClass('d-none');
                if (res.success) {
                    $('#detail_nama_outlet').text(res.data.nama_outlet);
                    $('#detail_kode_outlet').text(res.data.kode_outlet);
                    $('#detail_username').text('@' + res.data.username);
                    $('#detail_alamat').text(res.data.alamat_outlet || '-');
                    $('#detail_total_omzet').text('Rp ' + new Intl.NumberFormat('id-ID').format(res.data.total_omzet));
                    $('#detail_total_laporan').text(res.data.total_laporan + ' Laporan');
                    $('#detailContent').removeClass('d-none');
                } else {
                    $('#modalDetailOutlet').modal('hide');
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                $('#modalDetailOutlet').modal('hide');
                Swal.fire('Error', 'Gagal mengambil detail data outlet.', 'error');
            }
        });
    });

    // 5. Delete Outlet with SweetAlert2
    $(document).on('click', '.btn-delete-outlet', function() {
        const idOutlet = $(this).data('id');
        const namaOutlet = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Outlet?',
            text: `Apakah Anda yakin ingin menghapus outlet "${namaOutlet}"? Semua data omzet terkait juga akan terhapus.`,
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
                        Swal.fire('Error', 'Gagal menghapus data outlet.', 'error');
                    }
                });
            }
        });
    });
});
</script>
