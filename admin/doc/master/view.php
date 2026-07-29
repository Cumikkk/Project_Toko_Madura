<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch Master list with sub-counts
$sqlMasters = "
    SELECT u.id_users, u.nama_lengkap, u.username, u.no_hp,
           COUNT(DISTINCT inv.id_investor) as total_investor,
           COUNT(DISTINCT o.id_outlet) as total_outlet
    FROM users u
    LEFT JOIN investor inv ON inv.id_master = u.id_users
    LEFT JOIN outlet o ON (o.id_investor = inv.id_investor AND o.status = 'active')
    WHERE u.role = 'master'
    GROUP BY u.id_users
    ORDER BY u.nama_lengkap ASC
";
$masters = $db->query($sqlMasters);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Master Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Master</li>
        </ol>
    </div>
</div>

<!-- Main Table Card -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">List Master</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Master</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-master">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-center">Nama Lengkap</th>
                                <th class="text-center">Username</th>
                                <th class="text-center">No. HP</th>
                                <th class="text-center">Total Investor</th>
                                <th class="text-center" style="width: 15%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($masters && $masters->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $masters->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-start"><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                        <td class="text-start"><code><?= htmlspecialchars($row['username']) ?></code></td>
                                        <td class="text-center"><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6"><?= number_format($row['total_investor']) ?> Investor</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/create?id=<?= $row['id_users'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Master"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Master" onclick="deleteMaster(<?= $row['id_users'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>', <?= intval($row['total_investor'] ?? 0) ?>, <?= intval($row['total_outlet'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada data Master terdaftar.</td>
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
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-master')) {
        $('#table-master').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            language: {
                searchPlaceholder: 'Cari master...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'asc']]
        });
    }
});

function deleteMaster(id, nama, totalInvestor, totalOutlet) {
    let alertHtml = `
        <div class="text-start fs-14">
            <p class="text-muted mb-3">Tindakan ini akan menghapus akun Master <strong class="text-dark">${nama}</strong> beserta seluruh data yang terikat di bawahnya:</p>
            
            <div class="bg-light p-3 rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-user-circle text-info me-2 fs-16"></i>Akun Master (${nama})</span>
                    <span class="badge bg-info rounded-pill px-3">Master</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-handshake-o text-primary me-2 fs-16"></i>Akun Investor</span>
                    <span class="badge bg-primary rounded-pill px-3">${totalInvestor} Investor</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-building text-warning me-2 fs-16"></i>Outlet</span>
                    <span class="badge bg-warning text-dark rounded-pill px-3">${totalOutlet} Outlet</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fa fa-money text-danger me-2 fs-16"></i>
                    <span class="text-dark">Seluruh Laporan Omzet & Bagi Hasil</span>
                </div>
            </div>
            
            <p class="text-danger small mb-0 fw-semibold"><i class="fa fa-exclamation-triangle me-1"></i> Data yang dihapus bersifat permanen dan tidak dapat dikembalikan.</p>
        </div>
    `;

    Swal.fire({
        title: 'Hapus Master?',
        html: alertHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Master',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'px-4 py-2',
            cancelButton: 'px-4 py-2'
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menghapus data Master & data terkait',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/delete", { id_users: id, id: id }, function(resp) {
                if (resp.success) {
                    Swal.fire('Berhasil!', resp.message, 'success').then(function() { location.reload(); });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus data master', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
            });
        }
    });
}
</script>
