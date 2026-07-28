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

// Fetch investors list with Master Owner name
$investors = $db->query("
    SELECT i.*, u.nama_lengkap, u.username, u.no_hp,
           u_master.nama_lengkap as nama_master
    FROM investor i
    JOIN users u ON (u.id_users = i.id_users)
    LEFT JOIN users u_master ON (u_master.id_users = i.id_master)
    {$whereClause}
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">List Investor Toko Madura</h6>
                    <p class="text-muted card-sub-title mb-0">Daftar investor pemodal beserta persentase pembagian hasil mereka.</p>
                </div>
                <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Investor</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="investor-table">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 5%;">No</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>No HP</th>
                                <th>Kecamatan</th>
                                <th>Bagi Hasil (%)</th>
                                <th>Master Owner</th>
                                <th width="15%">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($investors && $investors->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $investors->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                        <td><code><?= htmlspecialchars($row['username']) ?></code></td>
                                        <td><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['kecamatan'] ?? '-') ?></td>
                                        <td class="text-center"><span class="badge bg-primary fs-6"><?= number_format($row['persen_bagian_investor'], 2, ',', '.') ?>%</span></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($row['nama_master'] ?? 'Master Owner') ?></span></td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create?id=<?= $row['id_investor'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Investor"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Investor" onclick="deleteInvestor(<?= $row['id_investor'] ?>, '<?= htmlspecialchars($row['nama_lengkap']) ?>')"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data investor terdaftar.</td>
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

function deleteInvestor(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus investor '" + name + "'?",
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

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/investor/delete", { id_investor: id, id: id }, function(resp) {
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
            }, 'json').fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan sistem saat menghapus data'
                });
            });
        }
    });
}
</script>
