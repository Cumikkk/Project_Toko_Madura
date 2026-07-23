<?php 
use App\Models\Helper;

if(!$adminPermissionCore->isHavePermission($moduleId, "create")) {
    $redirectUrl = \Config\Core\SystemInfo::app('ADMIN_URL') . '/admin/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Tambah Admin Baru</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(1) ?>/view">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mx-auto mb-3">
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Form Tambah Admin</h5>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-create-admin">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="add-fullname" class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control" id="add-fullname" name="add-fullname" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="add-username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" id="add-username" name="add-username" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="add-level" class="form-label fw-bold">Role / Level</label>
                                <select name="add-level" id="add-level" class="form-control" required>
                                    <option value="1">Programmer (Super Master)</option>
                                    <option value="2" selected>Master (Owner)</option>
                                    <option value="3">Admin Staf</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="add-password" class="form-label fw-bold">Password</label>
                                <input type="password" class="form-control" id="add-password" name="add-password" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 d-flex justify-content-end gap-2">
                            <a href="<?= pathbreadcrumb(1) ?>/view" class="btn btn-secondary">Batal</a>
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
                
            button.addClass('loading');
            $.post("/ajax/post/admin/create", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = resp.data?.redirect;
                    }
                });
            }, 'json');
        });
    });
</script>