<?php 
use App\Models\Admin;
use App\Models\Helper;
use Config\Core\SystemInfo;

if(!$adminPermissionCore->isHavePermission($moduleId, "update")) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/admin/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}

$adminId = Helper::form_input(!empty($_GET['c']) ? $_GET['c'] : ($_GET['b'] ?? 0));
$admin = Admin::findById($adminId);
if(!$admin) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/admin/view';
    die("<script>alert('ID Admin tidak valid'); location.href = '{$redirectUrl}'; </script>");
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Edit Data Admin</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/admin/view">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Data</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto mb-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Form Edit Data Admin</h5>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-update-admin">
                    <input type="hidden" name="admin_id" value="<?= $admin['ID_ADM']; ?>">
                    
                    <div class="row">
                        <!-- BARIS 1: NAMA LENGKAP ADMIN & NO. HP -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="fullname" class="form-label fw-bold">Nama Lengkap Admin <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Contoh: Fahrul Alfanani" value="<?= htmlspecialchars($admin['ADM_NAME'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label fw-bold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($admin['ADM_PHONE'] ?? '') ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 2: ROLE & USERNAME -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="level" class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                                <select name="level" id="level" class="form-control" required>
                                    <option value="1" selected>Admin (Programmer)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: admin_fahrul" value="<?= htmlspecialchars($admin['ADM_USER'] ?? '') ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 3: PASSWORD -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="password" class="form-label fw-bold">Password (Opsional)</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Biarkan kosong jika tidak diubah">
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/admin/view" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" data-original-text="Submit">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-update-admin').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = $(this).serialize();
                
            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/admin/update", data, (resp) => {
                button.removeClass('loading').prop('disabled', false);
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Data admin berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/admin/view";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian!',
                        text: resp.message || 'Gagal memperbarui data admin.'
                    });
                }
            }, 'json').fail(function(xhr) {
                button.removeClass('loading').prop('disabled', false);
                let errorMsg = 'Gagal terhubung ke server. Silakan coba lagi.';
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