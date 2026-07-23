<?php 
use App\Models\Admin;
use App\Models\Country;
use App\Models\Helper;

if(!$adminPermissionCore->isHavePermission($moduleId, "update")) {
    $redirectUrl = \Config\Core\SystemInfo::app('ADMIN_URL') . '/admin/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}

$adminId = Helper::form_input(!empty($_GET['c']) ? $_GET['c'] : ($_GET['b'] ?? 0));
$admin = Admin::findById($adminId);
if(!$admin) {
    $redirectUrl = \Config\Core\SystemInfo::app('ADMIN_URL') . '/admin/view';
    die("<script>alert('ID Admin tidak valid'); location.href = '{$redirectUrl}'; </script>");
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Update Admin</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(1) ?>/view">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Update</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Admin <b class="text-primary"><?= $admin['ADM_NAME'] ?></b></h5>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-update-admin">
                    <input type="hidden" name="admin_id" value="<?= $admin['ID_ADM']; ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="fullname" class="form-label">Fullname</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Fullname" value="<?= $admin['ADM_NAME'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?= $admin['ADM_USER'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="level" class="form-label">Role / Level</label>
                                <select name="level" id="level" class="form-control">
                                    <option value="1" <?= ($admin['ADM_LEVEL'] == 1)? "selected" : ""; ?>>Programmer (Super Master)</option>
                                    <option value="2" <?= ($admin['ADM_LEVEL'] == 2)? "selected" : ""; ?>>Master (Owner)</option>
                                    <option value="3" <?= ($admin['ADM_LEVEL'] == 3)? "selected" : ""; ?>>Admin Staf</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="email" class="form-label">Email (Opsional)</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email admin" value="<?= htmlspecialchars($admin['ADM_EMAIL'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label">No. HP / WhatsApp (Opsional)</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="No HP admin" value="<?= htmlspecialchars($admin['ADM_PHONE'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary" data-original-text="Submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Update Password</h5>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-update-password">
                    <input type="hidden" name="admin_id" value="<?= $adminId ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="new-password" class="form-control-label">Password Baru</label>
                                <div class="input-group">
                                    <input type="text" name="new-password" class="form-control" placeholder="Masukkan password baru (contoh: 123)" value="<?= Helper::generatePassword(); ?>" required>
                                    <button type="submit" class="input-group-text bg-primary" data-original-text="Update">Update</button>
                                </div>
                            </div>
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
                
            button.addClass('loading');
            $.post("/ajax/post/admin/update", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = resp.data?.redirect;
                    }
                })
            }, 'json');
        })

        $('#form-update-password').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = $(this).serialize();
                
            button.addClass('loading');
            $.post("/ajax/post/admin/updatePassword", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json');
        })
    })
</script>