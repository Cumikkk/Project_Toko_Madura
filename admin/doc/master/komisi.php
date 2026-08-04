<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch all Master accounts for dropdown select
$resMasters = $db->query("SELECT id_users, nama_lengkap, username FROM users WHERE role = 'master' ORDER BY nama_lengkap ASC");

// Fetch all Komisi records
$sqlKomisi = "
    SELECT km.*, u.nama_lengkap as nama_master, u.username as username_master
    FROM komisi_master km
    JOIN users u ON u.id_users = km.id_master
    ORDER BY km.tanggal_komisi DESC, km.id_komisi DESC
";
$listKomisi = $db->query($sqlKomisi);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Komisi & Reward Master Owner</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Komisi & Reward</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Riwayat Komisi & Reward Master</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModalKomisi()"><i class="fas fa-plus me-1"></i> Tambah Komisi Master</button>
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
                                <th class="text-center">Periode / Keterangan</th>
                                <th class="text-center">Nominal Komisi</th>
                                <th class="text-center">Catatan</th>
                                <th class="text-center" style="width: 12%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($listKomisi && $listKomisi->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $listKomisi->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= date("d/m/Y H:i", strtotime($row['tanggal_komisi'])) ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_master']) ?></strong>
                                            <br><small class="text-muted"><code>@<?= htmlspecialchars($row['username_master']) ?></code></small>
                                        </td>
                                        <td class="text-start"><strong><?= htmlspecialchars($row['periode']) ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-6">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-start"><small><?= htmlspecialchars($row['catatan'] ?? '-') ?></small></td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                                                    <button type="button" class="btn btn-success btn-sm text-white" title="Edit Komisi" 
                                                            onclick='editKomisi(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white" title="Hapus Komisi" 
                                                            onclick="deleteKomisi(<?= $row['id_komisi'] ?>, '<?= htmlspecialchars($row['nama_master'], ENT_QUOTES, 'UTF-8') ?>', 'Rp <?= number_format($row['nominal'], 0, ',', '.') ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat komisi master terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Komisi Master -->
<div class="modal fade" id="modal-komisi-master" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-komisi-title">Tambah Komisi Master</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-komisi-master" method="POST">
                <input type="hidden" name="id_komisi" id="komisi_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_master" class="form-label fw-bold">Master Owner <span class="text-danger">*</span></label>
                        <select class="form-control" id="komisi_id_master" name="id_master" required>
                            <option value="" disabled selected>-- Pilih Master Owner --</option>
                            <?php if ($resMasters && $resMasters->num_rows > 0) : ?>
                                <?php mysqli_data_seek($resMasters, 0); while ($m = $resMasters->fetch_assoc()) : ?>
                                    <option value="<?= $m['id_users'] ?>"><?= htmlspecialchars($m['nama_lengkap']) ?> (@<?= htmlspecialchars($m['username']) ?>)</option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_komisi" class="form-label fw-bold">Tanggal Transfer / Penyerahan <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="komisi_tanggal" name="tanggal_komisi" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="periode" class="form-label fw-bold">Periode / Keterangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="komisi_periode" name="periode" placeholder="Contoh: Bonus Rekrutmen Investor Ags 2026" required>
                    </div>

                    <div class="mb-3">
                        <label for="nominal" class="form-label fw-bold">Nominal Komisi (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0">Rp</span>
                            <input type="number" step="10000" min="0" class="form-control fw-bold border-start-0 border-end-0" id="komisi_nominal" name="nominal" placeholder="500000" value="500000" required>
                            <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                <div class="d-flex flex-column h-100" style="width: 24px;">
                                    <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepKomisi(50000)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+Rp 50.000)">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepKomisi(-50000)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-Rp 50.000)">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label fw-bold">Catatan / Pesan untuk Master (Opsional)</label>
                        <textarea class="form-control" id="komisi_catatan" name="catatan" rows="3" placeholder="Contoh: Terima kasih atas kontribusi aktif merekrut investor baru."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-komisi">Simpan Komisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function stepKomisi(amount) {
    let input = $('#komisi_nominal');
    let val = parseFloat(input.val()) || 0;
    let nextVal = Math.max(0, val + amount);
    input.val(nextVal);
}

function openModalKomisi() {
    $('#modal-komisi-title').text('Tambah Komisi Master');
    $('#form-komisi-master')[0].reset();
    $('#komisi_id').val('');
    $('#komisi_tanggal').val('<?= date("Y-m-d\TH:i") ?>');
    $('#komisi_nominal').val('500000');
    $('#modal-komisi-master').modal('show');
}

function editKomisi(data) {
    $('#modal-komisi-title').text('Edit Komisi Master');
    $('#komisi_id').val(data.id_komisi);
    $('#komisi_id_master').val(data.id_master);
    
    let t = new Date(data.tanggal_komisi);
    let isoStr = t.getFullYear() + '-' + String(t.getMonth()+1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0') + 'T' + String(t.getHours()).padStart(2, '0') + ':' + String(t.getMinutes()).padStart(2, '0');
    $('#komisi_tanggal').val(isoStr);
    
    $('#komisi_periode').val(data.periode);
    $('#komisi_nominal').val(parseInt(data.nominal));
    $('#komisi_catatan').val(data.catatan || '');
    $('#modal-komisi-master').modal('show');
}

function deleteKomisi(id, nama, nominal) {
    Swal.fire({
        title: 'Hapus Data Komisi?',
        html: `Apakah Anda yakin ingin menghapus data komisi untuk <strong>${nama}</strong> sebesar <strong>${nominal}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/komisi", { action: 'delete', id_komisi: id }, function(resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: resp.message || 'Data komisi berhasil dihapus.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => { location.reload(); });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus data komisi.', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Gagal terhubung ke server.', 'error');
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
                searchPlaceholder: 'Cari komisi master...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'desc']]
        });
    }

    $('#form-komisi-master').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btn-submit-komisi');
        btn.prop('disabled', true);

        $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/komisi", $(this).serialize(), function(resp) {
            btn.prop('disabled', false);
            if (resp.success) {
                $('#modal-komisi-master').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: resp.message || 'Data komisi berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => { location.reload(); });
            } else {
                Swal.fire('Gagal!', resp.message || 'Gagal menyimpan data komisi.', 'error');
            }
        }, 'json').fail(function() {
            btn.prop('disabled', false);
            Swal.fire('Error!', 'Gagal terhubung ke server.', 'error');
        });
    });
});
</script>
