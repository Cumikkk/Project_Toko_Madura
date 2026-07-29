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
    let alertHtml = '<div class="text-start">' +
        '<p class="mb-2 text-danger fw-bold"><i class="fa fa-exclamation-triangle me-1"></i> PERINGATAN HAPUS INVESTOR!</p>' +
        '<p class="mb-2">Menghapus investor <strong>' + name + '</strong> akan menghapus secara permanen:</p>' +
        '<ol class="ps-3 mb-3 text-dark">' +
            '<li>Seluruh <strong>Laporan Omzet</strong> & <strong>Rekap Bagi Hasil</strong> terikat</li>' +
            '<li>Sebanyak <strong>' + totalOutlet + ' Toko / Outlet</strong> milik investor ini (beserta akun kasirnya)</li>' +
            '<li>Profil & Akun User Investor <strong>(' + name + ')</strong></li>' +
        '</ol>' +
        '<p class="mb-0 text-muted fs-13">Apakah Anda yakin ingin menghapus investor ini beserta seluruh toko cabangnya?</p>' +
    '</div>';

    Swal.fire({
        title: 'Konfirmasi Hapus Investor',
        html: alertHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Investor & Cabangnya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                text: "Memproses penghapusan bertingkat...",
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
