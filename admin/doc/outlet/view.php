<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$loggedInLevel = intval($user['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($user['ADM_ID'] ?? 1);

// Role Filtering for Outlets:
// Programmer (Level 1): See all outlets nationally
// Master Owner (Level 2): See only outlets belonging to his Master ID
// Admin Staff (Level 3): See only outlets belonging to Master Owner
if ($loggedInLevel == 1) {
    $whereClause = "";
} elseif ($loggedInLevel == 2) {
    $whereClause = "WHERE inv.id_master = {$loggedInId}";
} else {
    $whereClause = "WHERE inv.id_master = 2";
}

// Fetch outlets list with investor and user details
$outlets = $db->query("
    SELECT o.*, u.nama_lengkap as pengelola_toko, u.no_hp as no_hp_toko, 
           inv_user.nama_lengkap as nama_investor, inv.persen_bagian_investor
    FROM outlet o
    LEFT JOIN users u ON (u.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users inv_user ON (inv_user.id_users = inv.id_users)
    {$whereClause}
    ORDER BY o.nama_outlet ASC
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Cabang Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Outlet</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Daftar Outlet & Pemetaan Pemodal</h6>
                    <p class="text-muted card-sub-title mb-0">Daftar seluruh cabang Toko Madura beserta investor pemodal di belakangnya.</p>
                </div>
                <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Outlet</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="outlet-table">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 5%;">No</th>
                                <th>Kode Outlet</th>
                                <th>Nama Toko / Cabang</th>
                                <th>Pengelola (Kasir)</th>
                                <th>No. HP</th>
                                <th>Investor Pemodal</th>
                                <th>Alamat Toko</th>
                                <th width="15%">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($outlets && $outlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $outlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_outlet']) ?></span></td>
                                        <td><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['pengelola_toko'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['no_hp_toko'] ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($row['nama_investor'])) : ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?> (<?= number_format($row['persen_bagian_investor'], 0) ?>%)</span>
                                            <?php else : ?>
                                                <span class="badge bg-warning">Belum Ada Pemodal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['alamat_outlet'] ?? '-') ?></td>
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
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data cabang toko terdaftar.</td>
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
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#outlet-table')) {
        $('#outlet-table').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            language: {
                searchPlaceholder: 'Cari outlet...',
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
            order: [[2, 'asc']]
        });
    }
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
