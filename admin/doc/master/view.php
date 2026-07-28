<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// 1. Counts for Top Stat Cards
$masterCount = 0;
$resMaster = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'master'");
if ($resMaster && $resMaster->num_rows > 0) {
    $masterCount = (int)$resMaster->fetch_assoc()['total'];
}

$investorCount = 0;
$resInv = $db->query("SELECT COUNT(*) as total FROM investor WHERE id_master IS NOT NULL");
if ($resInv && $resInv->num_rows > 0) {
    $investorCount = (int)$resInv->fetch_assoc()['total'];
}

$outletCount = 0;
$resOutlet = $db->query("
    SELECT COUNT(o.id_outlet) as total 
    FROM outlet o 
    JOIN investor inv ON inv.id_investor = o.id_investor 
    WHERE inv.id_master IS NOT NULL AND o.status = 'active'
");
if ($resOutlet && $resOutlet->num_rows > 0) {
    $outletCount = (int)$resOutlet->fetch_assoc()['total'];
}

// 2. Fetch Master list with sub-counts
$sqlMasters = "
    SELECT u.id_users, u.nama_lengkap, u.username, u.no_hp, u.created_at,
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
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Master Owner Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Master</li>
        </ol>
    </div>
</div>

<!-- Summary Metrics Cards (Gaya Dashboard RRFX) -->
<div class="row row-sm mb-3">
    <div class="col-sm-4 col-lg-4 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Total Master Owner</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-user-circle icon-size float-start text-primary"></i><span><?= number_format($masterCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-4 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Total Investor Dibawahi</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-handshake-o icon-size float-start text-warning"></i><span><?= number_format($investorCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-4 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Total Outlet Active</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-building icon-size float-start text-success"></i><span><?= number_format($outletCount) ?></span></h3>
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
                    <h5 class="card-title mb-0">List Master Owner</h5>
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
                                <th class="text-center">Nama Master Owner</th>
                                <th class="text-center">Username</th>
                                <th class="text-center">No. HP / WA</th>
                                <th class="text-center">Total Investor</th>
                                <th class="text-center">Total Outlet Active</th>
                                <th class="text-center">Tanggal Bergabung</th>
                                <th class="text-center" style="width: 15%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($masters && $masters->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $masters->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-start"><strong class="text-primary"><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                        <td class="text-start"><code><?= htmlspecialchars($row['username']) ?></code></td>
                                        <td class="text-center">
                                            <?php if (!empty($row['no_hp'])) : ?>
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['no_hp']) ?>" target="_blank" class="badge bg-success text-white px-2 py-1">
                                                    <i class="fa fa-whatsapp me-1"></i><?= htmlspecialchars($row['no_hp']) ?>
                                                </a>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6"><?= number_format($row['total_investor']) ?> Investor</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-6"><?= number_format($row['total_outlet']) ?> Toko</span>
                                        </td>
                                        <td class="text-center">
                                            <?= !empty($row['created_at']) ? date("d/m/Y H:i", strtotime($row['created_at'])) : '-' ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/create?id=<?= $row['id_users'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Master"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Master" onclick="deleteMaster(<?= $row['id_users'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>')"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data Master Owner terdaftar.</td>
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
                searchPlaceholder: 'Cari master owner...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'asc']]
        });
    }
});

function deleteMaster(id, nama) {
    Swal.fire({
        title: 'Hapus Master Owner?',
        text: "Apakah Anda yakin ingin menghapus akun Master Owner '" + nama + "'?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/delete", { id_users: id }, function(resp) {
                if (resp.success) {
                    Swal.fire('Dihapus!', resp.message, 'success').then(function() { location.reload(); });
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
