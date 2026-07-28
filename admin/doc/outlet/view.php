<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// 1. Fetch counts for stats cards
$activeCount = 0;
$resActive = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'active'");
if ($resActive && $resActive->num_rows > 0) {
    $activeCount = (int)$resActive->fetch_assoc()['total'];
}

$pendingCount = 0;
$resPending = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'pending'");
if ($resPending && $resPending->num_rows > 0) {
    $pendingCount = (int)$resPending->fetch_assoc()['total'];
}

$rejectCount = 0;
$resReject = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'reject'");
if ($resReject && $resReject->num_rows > 0) {
    $rejectCount = (int)$resReject->fetch_assoc()['total'];
}

// 2. Fetch Active Outlets
$sqlActive = "
    SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko,
           u_inv.nama_lengkap as nama_investor, inv.persen_bagian_investor
    FROM outlet o
    LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.status = 'active'
    ORDER BY o.nama_outlet ASC
";
$activeOutlets = $db->query($sqlActive);

// 3. Fetch Pending Outlets (Request Outlet)
$sqlPending = "
    SELECT o.*, u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.status = 'pending'
    ORDER BY o.id_outlet DESC
";
$pendingOutlets = $db->query($sqlPending);

// 4. Fetch Rejected Outlets
$sqlReject = "
    SELECT o.*, u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.status = 'reject'
    ORDER BY o.id_outlet DESC
";
$rejectedOutlets = $db->query($sqlReject);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Outlet Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Outlet</li>
        </ol>
    </div>
</div>

<!-- Summary Metrics Cards (Interactive Clickable Tabs) -->
<div class="row row-sm mb-3">
    <div class="col-sm-4 col-lg-4 mb-2">
        <div class="card custom-card outlet-stat-card active-card" id="card-active" data-tab="active">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted fw-bold"><i class="fa fa-check-circle icon-size float-start text-success me-2"></i>Outlet Aktif</h6>
                    <h3 class="text-end mb-0 text-success fw-bold"><span><?= $activeCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-4 mb-2">
        <div class="card custom-card outlet-stat-card" id="card-pending" data-tab="pending">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted fw-bold"><i class="fa fa-clock-o icon-size float-start text-warning me-2"></i>Request Masuk</h6>
                    <h3 class="text-end mb-0 text-warning fw-bold"><span><?= $pendingCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-4 mb-2">
        <div class="card custom-card outlet-stat-card" id="card-reject" data-tab="reject">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted fw-bold"><i class="fa fa-times-circle icon-size float-start text-danger me-2"></i>Request Ditolak</h6>
                    <h3 class="text-end mb-0 text-danger fw-bold"><span><?= $rejectCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title mb-0" id="table-card-title"><i class="fas fa-store text-success me-2"></i>List Outlet Aktif</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Outlet</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content" id="outlet-tab-content">
                    <!-- TAB 1: OUTLET AKTIF -->
                    <div class="tab-pane fade show active outlet-tab-pane" id="tab-active" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-outlet-active">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center" style="width: 5%;">No</th>
                                        <th class="text-center">Nama Toko</th>
                                        <th class="text-center">Nama Pengelola Toko</th>
                                        <th class="text-center">No. HP</th>
                                        <th class="text-center">Kecamatan</th>
                                        <th class="text-center">Investor</th>
                                        <th class="text-center">Tanggal Disetujui</th>
                                        <th class="text-center" style="width: 15%;">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($activeOutlets && $activeOutlets->num_rows > 0) : ?>
                                        <?php $no = 1; while ($row = $activeOutlets->fetch_assoc()) : ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td class="text-start"><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                                <td class="text-start"><?= htmlspecialchars($row['pengelola_toko'] ?? '-') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['no_hp_toko'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                                    <?php if (!empty($row['alamat_outlet'])) : ?>
                                                        <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-lihat-alamat" 
                                                                data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>" 
                                                                data-alamat="<?= htmlspecialchars(str_replace(["\r\n", "\r", "\n"], ' ', $row['alamat_outlet']), ENT_QUOTES, 'UTF-8') ?>" 
                                                                title="Lihat Alamat Lengkap">
                                                            <i class="fa fa-info-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (!empty($row['nama_investor'])) : ?>
                                                        <span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?> (<?= number_format($row['persen_bagian_investor'], 0) ?>%)</span>
                                                    <?php else : ?>
                                                        <span class="badge bg-warning text-dark">Belum Ada Investor</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?= !empty($row['tanggal_disetujui']) ? date("d/m/Y H:i", strtotime($row['tanggal_disetujui'])) : (!empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-') ?></td>
                                                <td class="text-center">
                                                    <div class="action d-flex justify-content-center gap-2">
                                                        <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/create?id=<?= $row['id_outlet'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Toko"><i class="fas fa-edit"></i></a>
                                                        <?php endif; ?>
                                                        <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                            <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Toko" onclick="deleteOutlet(<?= $row['id_outlet'] ?>, '<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>')"><i class="fas fa-trash"></i></button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada data toko aktif.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: REQUEST OUTLET (PENDING) -->
                    <div class="tab-pane fade d-none outlet-tab-pane" id="tab-pending" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-outlet-pending">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center" style="width: 5%;">No</th>
                                        <th class="text-center">Nama Outlet</th>
                                        <th class="text-center">Kecamatan</th>
                                        <th class="text-center">Investor</th>
                                        <th class="text-center">Biaya Langganan</th>
                                        <th class="text-center">Bukti Bayar</th>
                                        <th class="text-center">Tanggal Request</th>
                                        <th class="text-center" style="width: 15%;">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($pendingOutlets && $pendingOutlets->num_rows > 0) : ?>
                                        <?php $no = 1; while ($row = $pendingOutlets->fetch_assoc()) : ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td class="text-start"><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                                <td class="text-center">
                                                    <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                                    <?php if (!empty($row['alamat_outlet'])) : ?>
                                                        <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-lihat-alamat" 
                                                                data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>" 
                                                                data-alamat="<?= htmlspecialchars(str_replace(["\r\n", "\r", "\n"], ' ', $row['alamat_outlet']), ENT_QUOTES, 'UTF-8') ?>" 
                                                                title="Lihat Alamat Lengkap">
                                                            <i class="fa fa-info-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-start">
                                                    <strong><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></strong>
                                                    <?php if (!empty($row['no_hp_investor'])) : ?>
                                                        <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($row['no_hp_investor']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end fw-bold text-success">
                                                    Rp <?= number_format($row['nominal_biaya'], 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (!empty($row['bukti_pembayaran'])) : ?>
                                                        <a href="<?= SystemInfo::app('ADMIN_URL') . '/' . htmlspecialchars($row['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                            <i class="fas fa-image me-1"></i> Lihat Bukti
                                                        </a>
                                                    <?php else : ?>
                                                        <span class="badge bg-light text-dark">Belum ada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= !empty($row['tanggal_request']) ? date("d/m/Y H:i", strtotime($row['tanggal_request'])) : (!empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-') ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-success btn-sm btn-accept" data-id="<?= $row['id_outlet'] ?>" data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="fas fa-check me-1"></i> Setujui
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm btn-reject" data-id="<?= $row['id_outlet'] ?>" data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="fas fa-times me-1"></i> Tolak
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada request outlet pending.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: DITOLAK (REJECT) -->
                    <div class="tab-pane fade d-none outlet-tab-pane" id="tab-reject" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-outlet-reject">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center" style="width: 5%;">No</th>
                                        <th class="text-center">Nama Outlet</th>
                                        <th class="text-center">Kecamatan</th>
                                        <th class="text-center">Investor</th>
                                        <th class="text-center">Biaya Langganan</th>
                                        <th class="text-center">Alasan Penolakan</th>
                                        <th class="text-center">Tanggal Request</th>
                                        <th class="text-center">Tanggal Ditolak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($rejectedOutlets && $rejectedOutlets->num_rows > 0) : ?>
                                        <?php $no = 1; while ($row = $rejectedOutlets->fetch_assoc()) : ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td class="text-start"><strong class="text-danger"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                                <td class="text-center">
                                                    <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                                    <?php if (!empty($row['alamat_outlet'])) : ?>
                                                        <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-lihat-alamat" 
                                                                data-nama="<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>" 
                                                                data-alamat="<?= htmlspecialchars(str_replace(["\r\n", "\r", "\n"], ' ', $row['alamat_outlet']), ENT_QUOTES, 'UTF-8') ?>" 
                                                                title="Lihat Alamat Lengkap">
                                                            <i class="fa fa-info-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-start">
                                                    <strong><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></strong>
                                                    <?php if (!empty($row['no_hp_investor'])) : ?>
                                                        <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($row['no_hp_investor']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end text-muted">
                                                    Rp <?= number_format($row['nominal_biaya'], 0, ',', '.') ?>
                                                </td>
                                                <td class="text-start">
                                                    <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($row['alasan_penolakan'] ?? 'Tidak ada catatan') ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?= !empty($row['tanggal_request']) ? date("d/m/Y H:i", strtotime($row['tanggal_request'])) : (!empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-') ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= !empty($row['tanggal_ditolak']) ? date("d/m/Y H:i", strtotime($row['tanggal_ditolak'])) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada request outlet yang ditolak.</td>
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
</div>

<!-- Modal Reject Request Outlet -->
<div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-reject-outlet">
                <input type="hidden" name="id_outlet" id="reject_id_outlet">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Tolak Request Outlet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menolak request pembukaan toko <strong id="reject_nama_outlet"></strong>?</p>
                    <div class="form-group mb-0">
                        <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_penolakan" class="form-control" rows="3" placeholder="Masukkan alasan penolakan untuk investor..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i> Proses Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function switchOutletTab(tabKey) {
    // 1. Highlight card
    $('.outlet-stat-card').removeClass('active-card');
    $('#card-' + tabKey).addClass('active-card');

    // 2. Switch tab pane visibility directly
    $('.outlet-tab-pane').removeClass('show active').addClass('d-none');
    $('#tab-' + tabKey).removeClass('d-none').addClass('show active');

    // 3. Update table title
    if (tabKey === 'pending') {
        $('#table-card-title').html('<i class="fas fa-clock text-warning me-2"></i>List Request Outlet (Pending)');
    } else if (tabKey === 'reject') {
        $('#table-card-title').html('<i class="fas fa-times-circle text-danger me-2"></i>List Request Outlet (Ditolak)');
    } else {
        $('#table-card-title').html('<i class="fas fa-store text-success me-2"></i>List Outlet Aktif');
    }

    // 4. Adjust DataTables column width
    if ($.fn.DataTable) {
        setTimeout(() => {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }, 100);
    }
}

$(document).ready(function() {
    // Initialize DataTables for ALL THREE tables
    const tables = ['#table-outlet-active', '#table-outlet-pending', '#table-outlet-reject'];
    tables.forEach(tableId => {
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable({
                processing: true,
                deferRender: true,
                scrollX: true,
                lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
                language: {
                    searchPlaceholder: 'Cari outlet...',
                    sSearch: '',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
                },
                order: [[0, 'asc']]
            });
        }
    });

    // Clickable Stat Card Navigation Event Handler
    $(document).on('click', '.outlet-stat-card', function() {
        let tabKey = $(this).data('tab');
        switchOutletTab(tabKey);
    });

    // Auto switch tab if URL has ?tab=pending or ?tab=reject or hash #pending / #reject
    let urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'pending' || window.location.hash === '#pending') {
        switchOutletTab('pending');
    } else if (urlParams.get('tab') === 'reject' || window.location.hash === '#reject') {
        switchOutletTab('reject');
    }

    // Modal popup detail alamat outlet (Delegated event listener)
    $(document).on('click', '.btn-lihat-alamat', function(e) {
        e.preventDefault();
        let nama = $(this).attr('data-nama') || $(this).data('nama');
        let alamat = $(this).attr('data-alamat') || $(this).data('alamat');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Alamat Lengkap Outlet',
                html: '<p class="text-start mb-1"><strong>Outlet:</strong> ' + (nama || '-') + '</p><div class="p-3 bg-light rounded text-start"><i class="fa fa-map-marker me-2 text-danger"></i>' + (alamat || 'Belum ada alamat lengkap') + '</div>',
                icon: 'info',
                confirmButtonText: 'Tutup'
            });
        } else {
            alert('Outlet: ' + nama + '\nAlamat: ' + alamat);
        }
    });

    // Handle Accept Request Click
    $(document).on('click', '.btn-accept', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');

        Swal.fire({
            title: 'Setujui Request Outlet?',
            text: "Persetujuan ini akan mengaktifkan outlet " + nama + " secara resmi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui (Active)',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/request-outlet/accept", { id_outlet: id }, function(resp) {
                    if (resp.success) {
                        Swal.fire('Berhasil!', resp.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', resp.message || 'Gagal mengaktifkan outlet', 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
                });
            }
        });
    });

    // Handle Reject Request Click (Open Modal)
    $(document).on('click', '.btn-reject', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        $('#reject_id_outlet').val(id);
        $('#reject_nama_outlet').text(nama);
        $('#form-reject-outlet')[0].reset();
        $('#reject_id_outlet').val(id);

        let modalEl = document.getElementById('modalReject');
        if (modalEl) {
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // Handle Submit Form Reject
    $('#form-reject-outlet').on('submit', function(e) {
        e.preventDefault();
        let data = $(this).serialize();
        let btn = $(this).find('button[type="submit"]');

        btn.prop('disabled', true);
        $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/request-outlet/reject", data, function(resp) {
            btn.prop('disabled', false);
            if (resp.success) {
                let modalEl = document.getElementById('modalReject');
                let modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                Swal.fire('Berhasil!', resp.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal!', resp.message || 'Gagal menolak request outlet', 'error');
            }
        }, 'json').fail(function() {
            btn.prop('disabled', false);
            Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
        });
    });
});

function deleteOutlet(id, nama) {
    Swal.fire({
        title: 'Hapus Outlet?',
        text: 'Apakah Anda yakin ingin menghapus outlet ' + nama + '? Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/outlet/delete", { id_outlet: id }, function(resp) {
                if (resp.success) {
                    Swal.fire('Dihapus!', resp.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus outlet', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
            });
        }
    });
}
</script>
