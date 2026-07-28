<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch summary metrics
$pendingCount = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;
$activeCount  = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'active'")->fetch_assoc()['total'] ?? 0;
$rejectCount  = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'reject'")->fetch_assoc()['total'] ?? 0;

$totalRevenue = $db->query("SELECT IFNULL(SUM(nominal_biaya), 0) as total FROM outlet WHERE status = 'active'")->fetch_assoc()['total'] ?? 0;

// Fetch all outlet requests
$sqlRequests = "
    SELECT 
        o.*,
        u_kasir.nama_lengkap as pengelola,
        u_inv.nama_lengkap as nama_investor,
        u_inv.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN users u_kasir ON (u_kasir.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
    ORDER BY CASE WHEN o.status = 'pending' THEN 1 WHEN o.status = 'active' THEN 2 ELSE 3 END, o.id_outlet DESC
";

$requests = $db->query($sqlRequests);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Manajemen Request Outlet</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Request Outlet</li>
        </ol>
    </div>
</div>

<!-- Summary Cards (RRFX Default Template Style) -->
<div class="row row-sm mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Pending Request</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-clock-o icon-size float-start text-warning"></i><span><?= $pendingCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Outlet Active</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-check-circle icon-size float-start text-success"></i><span><?= $activeCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Request Rejected</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-times-circle icon-size float-start text-danger"></i><span><?= $rejectCount ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Biaya Langganan</h6>
                    <h4 class="text-end mb-0"><i class="fa fa-money icon-size float-start text-primary"></i><span>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Data Table -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Daftar Request Pembukaan Outlet</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="request-outlet-table">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-center">Nama Outlet</th>
                                <th class="text-center">Kecamatan</th>
                                <th class="text-center">Investor Pemodal</th>
                                <th class="text-center">Biaya Langganan</th>
                                <th class="text-center">Bukti Bayar</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Tanggal Request</th>
                                <th class="text-center" style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests && $requests->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $requests->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-start"><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                        <td class="text-center">
                                            <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                            <?php if (!empty($row['alamat_outlet'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-lihat-alamat-req" 
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
                                            <?php if ($row['status'] === 'pending') : ?>
                                                <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock me-1"></i> Pending</span>
                                            <?php elseif ($row['status'] === 'active') : ?>
                                                <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> Active</span>
                                            <?php else : ?>
                                                <span class="badge bg-danger fs-6" title="<?= htmlspecialchars($row['alasan_penolakan'] ?? '') ?>"><i class="fas fa-times-circle me-1"></i> Reject</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= !empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-' ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['status'] === 'pending') : ?>
                                                <button type="button" class="btn btn-success btn-sm btn-accept" data-id="<?= $row['id_outlet'] ?>" data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>">
                                                    <i class="fas fa-check me-1"></i> Accept
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm btn-reject" data-id="<?= $row['id_outlet'] ?>" data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>">
                                                    <i class="fas fa-times me-1"></i> Reject
                                                </button>
                                            <?php elseif ($row['status'] === 'reject' && !empty($row['alasan_penolakan'])) : ?>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="alert('Alasan Penolakan: <?= addslashes(htmlspecialchars($row['alasan_penolakan'])) ?>')">
                                                    <i class="fas fa-info-circle me-1"></i> Alasan
                                                </button>
                                            <?php else : ?>
                                                <span class="text-muted fs-12"><i class="fas fa-lock me-1"></i> Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada request outlet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
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
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#request-outlet-table')) {
        $('#request-outlet-table').DataTable({
            processing: true,
            scrollX: true,
            language: {
                searchPlaceholder: 'Cari request outlet...',
                sSearch: ''
            }
        });
    }

    // Modal popup detail alamat outlet
    $('.btn-lihat-alamat-req').on('click', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        Swal.fire({
            title: 'Alamat Lengkap Outlet',
            html: '<p class="text-start mb-1"><strong>Outlet:</strong> ' + nama + '</p><div class="p-3 bg-light rounded text-start"><i class="fa fa-map-marker me-2 text-danger"></i>' + (alamat || 'Belum ada alamat lengkap') + '</div>',
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    });

    // Handle Accept Click
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
            confirmButtonText: 'Ya, Setujui (Active)'
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

    // Handle Reject Click
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
</script>
