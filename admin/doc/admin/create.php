<?php 
use Config\Core\SystemInfo;

if(!$adminPermissionCore->isHavePermission($moduleId, "create")) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/admin/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Registrasi Admin Baru</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/admin/view">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Registrasi</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto mb-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Form Registrasi Admin</h5>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-create-admin">
                    <div class="row">
                        <!-- BARIS 1: NAMA LENGKAP ADMIN & NO. HP -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="add-fullname" class="form-label fw-bold">Nama Lengkap Admin</label>
                                <input type="text" class="form-control" id="add-fullname" name="add-fullname" placeholder="Contoh: Fahrul Alfanani" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="add-phone" class="form-label fw-bold">No. HP / WhatsApp (Opsional)</label>
                                <input type="text" class="form-control" id="add-phone" name="add-phone" placeholder="Contoh: 081234567890">
                            </div>
                        </div>

                        <!-- BARIS 2: ROLE & USERNAME -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="add-level" class="form-label fw-bold">Role</label>
                                <select name="add-level" id="add-level" class="form-control" required>
                                    <option value="1" selected>Admin (Programmer)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="add-username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" id="add-username" name="add-username" placeholder="Contoh: admin_fahrul" required>
                            </div>
                        </div>

                        <!-- BARIS 3: PASSWORD -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="add-password" class="form-label fw-bold">Password</label>
                                <input type="password" class="form-control" id="add-password" name="add-password" placeholder="Masukkan password login" required>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/admin/view" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" data-original-text="Submit">Simpan Admin</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-create-admin').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = $(this).serialize();
                
            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/admin/create", data, (resp) => {
                button.removeClass('loading').prop('disabled', false);
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Data admin berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/admin/view";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian!',
                        text: resp.message || 'Gagal menyimpan data admin.'
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