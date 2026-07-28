<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$idMaster = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit   = ($idMaster > 0);
$masterData = null;

if ($isEdit) {
    if (!$adminPermissionCore->isHavePermission($moduleId, "update")) {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/view';
        die("<script>location.href = '{$redirectUrl}';</script>");
    }
    $res = $db->query("SELECT * FROM users WHERE id_users = {$idMaster} AND role = 'master'");
    if ($res && $res->num_rows > 0) {
        $masterData = $res->fetch_assoc();
    } else {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/view';
        die("<script>alert('Data Master tidak ditemukan!'); location.href = '{$redirectUrl}';</script>");
    }
} else {
    if (!$adminPermissionCore->isHavePermission($moduleId, "create")) {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/view';
        die("<script>location.href = '{$redirectUrl}';</script>");
    }
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Master Owner" : "Tambah Master Owner Baru" ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/view">Master</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit" : "Tambah" ?></li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mx-auto mb-4">
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fa fa-user-circle text-primary me-2"></i><?= $isEdit ? "Form Edit Master Owner" : "Form Tambah Master Owner" ?></h5>
            </div>
            <div class="card-body">
                <form id="form-master">
                    <input type="hidden" name="id_users" value="<?= $isEdit ? $masterData['id_users'] : '' ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Nama Lengkap Master Owner <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: H. Ahmad Subagyo" value="<?= $isEdit ? htmlspecialchars($masterData['nama_lengkap']) : '' ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: master_ahmad" value="<?= $isEdit ? htmlspecialchars($masterData['username']) : '' ?>" required>
                        <small class="text-muted">Username digunakan untuk login ke Portal Client.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">No. HP / WhatsApp (Opsional)</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" value="<?= $isEdit ? htmlspecialchars($masterData['no_hp'] ?? '') : '' ?>">
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Password <?= $isEdit ? '<span class="text-muted fw-normal">(Kosongkan jika tidak ingin diubah)</span>' : '<span class="text-danger">*</span>' ?></label>
                        <input type="password" name="password" class="form-control" placeholder="<?= $isEdit ? 'Masukkan password baru jika ingin mengubah' : 'Masukkan password' ?>" <?= $isEdit ? '' : 'required' ?>>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/view" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= $isEdit ? "Simpan Perubahan" : "Tambah Master" ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#form-master').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        var btn  = $(this).find('button[type="submit"]');

        btn.prop('disabled', true);
        $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/create", data, function(resp) {
            btn.prop('disabled', false);
            if (resp.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: resp.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    location.href = "<?= SystemInfo::app('ADMIN_URL') ?>/master/view";
                });
            } else {
                Swal.fire('Gagal!', resp.message || 'Gagal menyimpan data master', 'error');
            }
        }, 'json').fail(function() {
            btn.prop('disabled', false);
            Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
        });
    });
});
</script>
