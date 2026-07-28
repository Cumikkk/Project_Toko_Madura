<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$loggedInLevel = intval($user['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($user['ADM_ID'] ?? 1);

// Role Filtering for Outlets:
if ($loggedInLevel == 1) {
    $whereClause = "";
    $whereClauseInv = "";
} elseif ($loggedInLevel == 2) {
    $whereClause = "WHERE inv.id_master = {$loggedInId}";
    $whereClauseInv = "AND inv.id_master = {$loggedInId}";
} else {
    $whereClause = "WHERE inv.id_master IN (SELECT id_users FROM users WHERE role = 'master')";
    $whereClauseInv = "AND inv.id_master IN (SELECT id_users FROM users WHERE role = 'master')";
}

// Fetch Metrics Summary
$activeCount  = $db->query("SELECT COUNT(*) as total FROM outlet o LEFT JOIN investor inv ON inv.id_investor = o.id_investor WHERE o.status = 'active' {$whereClauseInv}")->fetch_assoc()['total'] ?? 0;
$pendingCount = $db->query("SELECT COUNT(*) as total FROM outlet o LEFT JOIN investor inv ON inv.id_investor = o.id_investor WHERE o.status = 'pending' {$whereClauseInv}")->fetch_assoc()['total'] ?? 0;
$rejectCount  = $db->query("SELECT COUNT(*) as total FROM outlet o LEFT JOIN investor inv ON inv.id_investor = o.id_investor WHERE o.status = 'reject' {$whereClauseInv}")->fetch_assoc()['total'] ?? 0;

// 1. Fetch Active Outlets
$activeOutlets = $db->query("
    SELECT o.*, u.nama_lengkap as pengelola_toko, u.no_hp as no_hp_toko, 
           inv_user.nama_lengkap as nama_investor, inv.persen_bagian_investor
    FROM outlet o
    LEFT JOIN users u ON (u.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users inv_user ON (inv_user.id_users = inv.id_users)
    {$whereClause} " . ($whereClause ? "AND" : "WHERE") . " o.status = 'active'
    ORDER BY o.nama_outlet ASC
");

// 2. Fetch Pending Outlets
$pendingOutlets = $db->query("
    SELECT o.*, u.nama_lengkap as pengelola_toko, u.no_hp as no_hp_toko, 
           inv_user.nama_lengkap as nama_investor, inv_user.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN users u ON (u.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users inv_user ON (inv_user.id_users = inv.id_users)
    {$whereClause} " . ($whereClause ? "AND" : "WHERE") . " o.status = 'pending'
    ORDER BY o.id_outlet DESC
");

// 3. Fetch Rejected Outlets
$rejectedOutlets = $db->query("
    SELECT o.*, u.nama_lengkap as pengelola_toko, u.no_hp as no_hp_toko, 
           inv_user.nama_lengkap as nama_investor, inv_user.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN users u ON (u.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users inv_user ON (inv_user.id_users = inv.id_users)
    {$whereClause} " . ($whereClause ? "AND" : "WHERE") . " o.status = 'reject'
    ORDER BY o.id_outlet DESC
");
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

<!-- Summary Metrics Cards (3 Cards Layout) -->
<div class="row row-sm mb-3">
    <div class="col-sm-4 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Outlet Aktif</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-check-circle icon-size float-start text-success"></i><span><?= $activeCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Request Masuk</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-clock-o icon-size float-start text-warning"></i><span><?= $pendingCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Request Ditolak</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-times-circle icon-size float-start text-danger"></i><span><?= $rejectCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card with Navigation Tabs -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title mb-0">List Outlet</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Outlet</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Navigation Tabs -->
                <ul class="nav nav-pills mb-3 gap-2" id="outlet-tab-list" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-bordered-btn active" id="active-tab" data-bs-toggle="pill" data-bs-target="#tab-active" type="button" role="tab" aria-controls="tab-active" aria-selected="true">
                            <i class="fas fa-store me-1"></i> Outlet Aktif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-bordered-btn" id="pending-tab" data-bs-toggle="pill" data-bs-target="#tab-pending" type="button" role="tab" aria-controls="tab-pending" aria-selected="false">
                            <i class="fas fa-clock me-1 text-warning"></i> Request Outlet
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-bordered-btn" id="reject-tab" data-bs-toggle="pill" data-bs-target="#tab-reject" type="button" role="tab" aria-controls="tab-reject" aria-selected="false">
                            <i class="fas fa-times-circle me-1 text-danger"></i> Ditolak
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="outlet-tab-content">
                    <!-- TAB 1: OUTLET AKTIF -->
                    <div class="tab-pane fade show active" id="tab-active" role="tabpanel" aria-labelledby="active-tab">
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
                                                                data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                                data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
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
                                                            <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Toko" onclick="deleteOutlet(<?= $row['id_outlet'] ?>, '<?= htmlspecialchars($row['nama_outlet']) ?>')"><i class="fas fa-trash"></i></button>
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
                    <div class="tab-pane fade" id="tab-pending" role="tabpanel" aria-labelledby="pending-tab">
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
                                                                data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                                data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
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
                                                        <button type="button" class="btn btn-success btn-sm btn-accept" data-id="<?= $row['id_outlet'] ?>" data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>">
                                                            <i class="fas fa-check me-1"></i> Setujui
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm btn-reject" data-id="<?= $row['id_outlet'] ?>" data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>">
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
                    <div class="tab-pane fade" id="tab-reject" role="tabpanel" aria-labelledby="reject-tab">
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
                                                                data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                                data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
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
$(document).ready(function() {
    // Initialize DataTables for ALL THREE tabs so show entries & pagination exist everywhere
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

    // Fix DataTables column recalculation on Tab Switch
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // Auto switch tab if URL has ?tab=pending or ?tab=reject
    let urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'pending' || window.location.hash === '#pending') {
        let tabBtn = document.querySelector('#pending-tab');
        if (tabBtn) {
            let tab = new bootstrap.Tab(tabBtn);
            tab.show();
        }
    } else if (urlParams.get('tab') === 'reject' || window.location.hash === '#reject') {
        let tabBtn = document.querySelector('#reject-tab');
        if (tabBtn) {
            let tab = new bootstrap.Tab(tabBtn);
            tab.show();
        }
    }

    // Modal popup detail alamat outlet
    $('.btn-lihat-alamat').on('click', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        Swal.fire({
            title: 'Alamat Lengkap Outlet',
            html: '<p class="text-start mb-1"><strong>Outlet:</strong> ' + nama + '</p><div class="p-3 bg-light rounded text-start"><i class="fa fa-map-marker me-2 text-danger"></i>' + (alamat || 'Belum ada alamat lengkap') + '</div>',
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    });

    // Handle Accept Request Click
    $('.btn-accept').on('click', function() {
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
                        Swal.fire('Gagal!', resp.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Handle Reject Request Click
    $('.btn-reject').on('click', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        $('#reject_id_outlet').val(id);
        $('#reject_nama_outlet').text(nama);
        $('#modalReject').modal('show');
    });

    // Handle Form Reject Submit
    $('#form-reject-outlet').on('submit', function(e) {
        e.preventDefault();
        let data = $(this).serialize();
        $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/request-outlet/reject", data, function(resp) {
            $('#modalReject').modal('hide');
            if (resp.success) {
                Swal.fire('Berhasil!', resp.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal!', resp.message, 'error');
            }
        }, 'json');
    });
});

function deleteOutlet(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus toko cabang '" + name + "'?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                text: "Loading...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/outlet/delete", { id: id }, function(resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: resp.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: resp.message
                    });
                }
            }, 'json');
        }
    });
}
</script>
