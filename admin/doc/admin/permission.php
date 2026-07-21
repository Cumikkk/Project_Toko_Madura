<?php 
use App\Models\Helper;
use App\Models\Admin;

try {
    $_SESSION['show_dev_menu'] = true;
    $idAdmin = Helper::form_input($_GET['c'] ?? "me");
    if(empty($idAdmin)) {
        die("<script>alert('Invalid Admin ID'); location.href = '/admins'; </script>");
    }
    
    /** List Permission Module */
    $idAdmin = ($idAdmin == "me")? $user['ID_ADM'] : $idAdmin;
    $dataAdmin = Admin::findById($idAdmin);
    if(empty($dataAdmin)) {
        die("<script>alert('Admin tidak ditemukan'); location.href = '/admins'; </script>");
    }

    $authorizeModule = $adminPermissionCore->getModule_and_Permissions($idAdmin);

} catch (Exception $e) {
    throw $e;
}
?>

<div class="row">
    <div class="col-md-9 mx-auto mt-4">
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap align-items-center">
                    <div>
                        <p class="mb-0">Edit Permission untuk admin <b class="text-primary"><?php echo $dataAdmin['ADM_NAME'] ?></b></p>
                        <p class="mb-0">Role <b class="text-primary"><?php echo $dataAdmin['ADMROLE_NAME'] ?></b></p>
                        <p><i>*Perubahan disimpan saat check / uncheck</i></p>
                        <input type="hidden" name="admin_id" value="<?= $idAdmin ?>">
                    </div>    
                </div>
            </div>
            <div class="card-body">
                <?php foreach($authorizeModule as $group) : ?>
                    <?php if($group['min_level'] >= $dataAdmin['ADM_LEVEL']) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead style="background-color: grey !important;">
                                    <tr>
                                        <th class="text-white" colspan="2">Module & Permission</th>
                                        <th class="text-white" width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($group['modules'] as $module) : ?>
                                        <tr><td colspan="3" class="fw-bold"><?= ucwords($group['group']) ?> / <?= ucwords($module['module']) ?></td></tr>
                                        <?php foreach($module['permission'] as $perm) : ?>
                                            <tr>
                                                <td width="5%">-</td>
                                                <td><?= $perm['desc'] ?></td>
                                                <td>
                                                    <div class="form-group mb-3">
                                                        <label class="mb-0">
                                                            <input type="checkbox" name="input[]" value="<?= $perm['permission_id'] ?>" class="custom-switch-input" <?= ($perm['status'])? "checked" : "" ?> />
                                                            <span class="custom-switch-indicator border-dark custom-switch-indicator-md"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('input[type="checkbox"]').on('change', function(evt) {
            let target = $(evt.currentTarget);
            if(target) {
                let data = {
                    permission_id: target.val(), 
                    admin_id: $('input[name="admin_id"]').val(),
                    status: target.is(':checked')
                }

                $.post("/ajax/post/admin/permission/update", data, function(resp) {
                    if(!resp.success) {
                        Swal.fire(resp.alert).then(() => {
                            location.reload();
                        });
                    }
                }, 'json')
            }
        })
    })
</script>