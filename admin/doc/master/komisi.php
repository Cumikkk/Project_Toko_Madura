<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch all Komisi records
$sqlKomisi = "
    SELECT km.*, u.nama_lengkap as nama_master, u.username as username_master
    FROM komisi_master km
    JOIN users u ON u.id_users = km.id_master
    ORDER BY km.tgl_transfer DESC, km.id_komisi DESC
";
$listKomisi = $db->query($sqlKomisi);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Komisi Master Owner</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Komisi</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Riwayat Komisi Master</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi_create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Komisi Master</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-komisi-master">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-center">Tanggal Transfer</th>
                                <th class="text-center">Nama Master</th>
                                <th class="text-center">Nominal Komisi</th>
                                <th class="text-center">Bukti Bayar</th>
                                <th class="text-center">Catatan</th>
                                <th class="text-center" style="width: 12%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($listKomisi && $listKomisi->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $listKomisi->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= date("d/m/Y H:i", strtotime($row['tgl_transfer'])) ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_master']) ?></strong>
                                            <br><small class="text-muted"><code>@<?= htmlspecialchars($row['username_master']) ?></code></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-6">Rp <?= number_format($row['nominal_transfer_komisi'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($row['bukti_pembayaran'])) : ?>
                                                <?php $fileExt = strtolower(pathinfo($row['bukti_pembayaran'], PATHINFO_EXTENSION)); ?>
                                                <?php if ($fileExt === 'pdf') : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/image-proxy.php?file=<?= urlencode($row['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-file-pdf me-1"></i> Lihat PDF
                                                    </a>
                                                <?php else : ?>
                                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                                            onclick="previewBuktiKomisi('<?= htmlspecialchars($row['bukti_pembayaran'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['nama_master'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['catatan'] ?? '-', ENT_QUOTES) ?>', 'Rp <?= number_format($row['nominal_transfer_komisi'], 0, ',', '.') ?>')">
                                                        <i class="fas fa-image me-1"></i> Lihat Bukti
                                                    </button>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="badge bg-light text-dark">Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start"><small><?= htmlspecialchars($row['catatan'] ?? '-') ?></small></td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi_create?id=<?= $row['id_komisi'] ?>" class="btn btn-success btn-sm text-white" title="Edit Komisi"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white" title="Hapus Komisi" 
                                                            onclick="deleteKomisi(<?= $row['id_komisi'] ?>, '<?= htmlspecialchars($row['nama_master'], ENT_QUOTES, 'UTF-8') ?>', 'Rp <?= number_format($row['nominal_transfer_komisi'], 0, ',', '.') ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada riwayat komisi master terdaftar.</td>
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
function previewBuktiKomisi(filePath, namaMaster, catatan, nominal) {
    if (!filePath) {
        Swal.fire('Informasi', 'Bukti pembayaran belum diunggah.', 'info');
        return;
    }
    var adminUrl = '<?= SystemInfo::app("ADMIN_URL") ?>';
    var proxyUrl = adminUrl + '/image-proxy.php?file=' + encodeURIComponent(filePath);
    var ext = filePath.split('.').pop().toLowerCase();
    if (ext === 'pdf') {
        window.open(proxyUrl, '_blank');
        return;
    }

    var infoHtml = '<div class="text-start bg-light p-3 rounded mb-3" style="font-size:13.5px; border:1px solid #e9ecef;">'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-user-circle text-primary me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Master Owner:</span>'
        + '  <span class="text-dark fw-semibold">' + (namaMaster || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-calendar-check-o text-success me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Catatan:</span>'
        + '  <span class="text-dark">' + (catatan || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center">'
        + '  <i class="fa fa-money text-warning me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nominal Komisi:</span>'
        + '  <span class="text-success fw-bold">' + nominal + '</span>'
        + '</div>'
        + '</div>';

    Swal.fire({
        title: '<i class="fa fa-file-text-o me-2 text-info"></i>Bukti Pembayaran Komisi Master',
        html: infoHtml
            + '<img src="' + proxyUrl + '" '
            + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
            + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\'><i class=\\\'fa fa-exclamation-triangle me-1\\\'></i> Gambar gagal dimuat</p>\'">',
        showCloseButton: true,
        showConfirmButton: false,
        scrollbarPadding: false,
        heightAuto: false,
        width: 640
    });
}

function deleteKomisi(id, master, nominal) {
    Swal.fire({
        title: 'Hapus Record Komisi?',
        text: `Hapus komisi untuk Master ${master} sebesar ${nominal}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Komisi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menghapus data komisi',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/komisi", { action: 'delete', id_komisi: id }, function(resp) {
                if (resp.success) {
                    Swal.fire('Berhasil!', resp.message, 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus data komisi', 'error');
                }
            }, 'json').fail(() => {
                Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
            });
        }
    });
}

$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-komisi-master')) {
        $('#table-komisi-master').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            language: {
                searchPlaceholder: 'Cari komisi...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'desc']]
        });
    }
});
</script>
