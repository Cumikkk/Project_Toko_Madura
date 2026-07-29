<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$loggedInLevel = intval($user['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($user['ADM_ID'] ?? 1);

// Role Filtering:
if ($loggedInLevel == 1) {
    $whereClause = "";
} elseif ($loggedInLevel == 2) {
    $whereClause = "WHERE i.id_master = {$loggedInId}";
} else {
    $whereClause = "WHERE i.id_master IN (SELECT id_users FROM users WHERE role = 'master')";
}

// Fetch investors list with Master Owner name and active outlet counts
$investors = $db->query("
    SELECT i.*, u.nama_lengkap, u.username, u.no_hp,
           u_master.nama_lengkap as nama_master,
           COUNT(DISTINCT o.id_outlet) as total_outlet
    FROM investor i
    JOIN users u ON (u.id_users = i.id_users)
    LEFT JOIN users u_master ON (u_master.id_users = i.id_master)
    LEFT JOIN outlet o ON (o.id_investor = i.id_investor AND o.status = 'active')
    {$whereClause}
    GROUP BY i.id_investor
    ORDER BY u.nama_lengkap ASC
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Investor Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Investor</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">List Investor</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Investor</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="investor-table">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-center">Nama Lengkap</th>
                                <th class="text-center">Username</th>
                                <th class="text-center">No. HP</th>
                                <th class="text-center">Kecamatan</th>
                                <th class="text-center">Bagi Hasil (%)</th>
                                <th class="text-center">Master Owner</th>
                                <th class="text-center">Total Outlet Active</th>
                                <th class="text-center">Tanggal Bergabung</th>
                                <th class="text-center" width="15%">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($investors && $investors->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $investors->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-start"><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                        <td class="text-start"><code><?= htmlspecialchars($row['username']) ?></code></td>
                                        <td class="text-center"><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                            <?php if (!empty($row['alamat_investor'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-lihat-alamat" 
                                                        data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>" 
                                                        data-alamat="<?= htmlspecialchars($row['alamat_investor']) ?>" 
                                                        title="Lihat Alamat Lengkap">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><span class="badge bg-primary fs-6"><?= number_format($row['persen_bagian_investor'], 2, ',', '.') ?>%</span></td>
                                        <td class="text-center"><span class="badge bg-info"><?= htmlspecialchars($row['nama_master'] ?? 'Master Owner') ?></span></td>
                                        <td class="text-center"><span class="badge bg-success fs-6"><?= number_format($row['total_outlet'] ?? 0) ?> Toko</span></td>
                                        <td class="text-center"><?= !empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-' ?></td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create?id=<?= $row['id_investor'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Investor"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Investor" onclick="deleteInvestor(<?= $row['id_investor'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>', <?= intval($row['total_outlet'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Belum ada data investor terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#investor-table')) {
        $('#investor-table').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            language: {
                searchPlaceholder: 'Cari investor...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            },
            order: [[1, 'asc']]
        });
    }

    // Modal popup detail alamat investor
    $('.btn-lihat-alamat').on('click', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        Swal.fire({
            title: 'Alamat Lengkap Investor',
            html: '<p class="text-start mb-1"><strong>Investor:</strong> ' + nama + '</p><div class="p-3 bg-light rounded text-start"><i class="fa fa-map-marker me-2 text-danger"></i>' + alamat + '</div>',
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    });
});

function deleteInvestor(id, name, totalOutlet) {
    let alertHtml = `
        <div class="text-start fs-14">
            <p class="text-muted mb-3">Tindakan ini akan menghapus akun Investor <strong class="text-dark">${name}</strong> beserta seluruh outlet di bawah kepemilikannya:</p>
            
            <div class="bg-light p-3 rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-handshake-o text-primary me-2 fs-16"></i>Akun Investor (${name})</span>
                    <span class="badge bg-primary rounded-pill px-3">Investor</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-building text-warning me-2 fs-16"></i>Outlet di Bawah Kepemilikannya</span>
                    <span class="badge bg-warning text-dark rounded-pill px-3">${totalOutlet} Outlet</span>
                </div>
                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                    <i class="fa fa-money text-danger me-2 fs-16"></i>
                    <span class="text-dark">Riwayat Laporan Omzet & Rekap Bagi Hasil</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fa fa-user-times text-danger me-2 fs-16"></i>
                    <span class="text-dark">Akun Kasir Outlet</span>
                </div>
            </div>
            
            <p class="text-danger small mb-0 fw-semibold"><i class="fa fa-exclamation-triangle me-1"></i> Data yang dihapus bersifat permanen dan tidak dapat dikembalikan.</p>
        </div>
    `;

    Swal.fire({
        title: 'Hapus Investor?',
        html: alertHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Investor',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'px-4 py-2',
            cancelButton: 'px-4 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: "Sedang menghapus investor & outlet terkait",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/investor/delete", { id_investor: id, id: id }, function(resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus data investor', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
            });
        }
    });
}
</script>
