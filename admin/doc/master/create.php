<?php
use App\Models\Master;
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$idMaster = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['c']) ? intval($_GET['c']) : 0);
$isEdit   = ($idMaster > 0);
$masterData = null;

if ($isEdit) {
    if (!$adminPermissionCore->isHavePermission($moduleId, "update")) {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/view';
        die("<script>location.href = '{$redirectUrl}';</script>");
    }
    $masterData = Master::getMasterById($idMaster);
    if (!$masterData) {
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
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Data Master" : "Registrasi Master Baru"; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/view">Master</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit Data" : "Registrasi"; ?></li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto mb-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title"><?= $isEdit ? "Form Edit Data Master" : "Form Registrasi Master"; ?></h5>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-create-master">
                    <?php if ($isEdit) : ?>
                        <input type="hidden" name="id_users" value="<?= $idMaster; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- BARIS 1: NAMA LENGKAP & NO. HP -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="nama_lengkap" class="form-label fw-bold">Nama Lengkap Master <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Contoh: Haji Ahmad Madura" value="<?= htmlspecialchars($masterData['nama_lengkap'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label fw-bold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($masterData['no_hp'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 2: KECAMATAN & ALAMAT LENGKAP -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="kecamatan" class="form-label fw-bold">Kecamatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kecamatan" name="kecamatan" placeholder="Contoh: Bangkalan" value="<?= htmlspecialchars($masterData['kecamatan'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="alamat" class="form-label fw-bold">Alamat Lengkap Master <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Contoh: Jl. Trunojoyo No. 10, Bangkalan" value="<?= htmlspecialchars($masterData['alamat_lengkap'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 3: USERNAME & PASSWORD -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: master_ahmad" value="<?= htmlspecialchars($masterData['username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="password" class="form-label fw-bold">Password <?= $isEdit ? '(Opsional)' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="<?= $isEdit ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password login'; ?>" <?= $isEdit ? "" : "required"; ?>>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/view" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" data-original-text="Submit"><?= $isEdit ? "Simpan Perubahan" : "Simpan Master"; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-create-master').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = $(this).serialize();
                
            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/create", data, (resp) => {
                button.removeClass('loading').prop('disabled', false);
                if (resp.success) {
                    let isEdit = $('input[name="id_users"]').val() ? true : false;
                    let defaultSuccessMsg = isEdit ? 'Data master berhasil diperbarui.' : 'Data master berhasil ditambahkan.';
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || defaultSuccessMsg,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/master/view";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian!',
                        text: resp.message || 'Gagal menyimpan data master.'
                    });
                }
            }, 'json').fail(function(xhr) {
                button.removeClass('loading').prop('disabled', false);
                let errorMsg = 'Terjadi kendala pada server (atau sesi Anda habis). Silakan coba lagi.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian!',
                    text: errorMsg
                });
            });
        });
    });
</script>
