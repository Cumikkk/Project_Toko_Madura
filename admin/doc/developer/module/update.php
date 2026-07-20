<?php if($adminPermissionCore->isHavePermission($filePermission['module_id'], "update")) : ?>
    <?php
    $listGrup = $adminPermissionCore->availableGroup();
    $permissionModule = App\Factory\PermissionModuleFactory::init();
    $moduleId = App\Models\Helper::form_input($_GET['d'] ?? "");
    $module = $permissionModule->findModuleById($_GET['d']);
    if(!$module) {
        die("<script>alert('Invalid Module'); location.href = '/developer/module'; </script>");
    }

    $permissionsss = $permissionModule->findPermissionByModuleId($module['id']);
    ?>

    <div class="page-header">
        <div>
            <h2 class="main-content-title tx-24 mg-b-5">Update <b class="text-primary"><?= ucwords($module['module']) ?></b></h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
                <li class="breadcrumb-item">Developer</li>
                <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(1) ?>/module/view">Module</a></li>
                <li class="breadcrumb-item active" aria-current="page">Update</li>
            </ol>
        </div>
    </div>


    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-priamry">Form Update</h5>
                </div>
                <div class="card-body">
                    <form method="post" id="update-module">
                        <input type="hidden" name="edit_m_id" id="edit_m_id" value="<?= $moduleId; ?>">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_m_name" class="form-control-label">Nama</label>
                                    <input type="text" name="edit_m_name" id="edit_m_name" class="form-control" placeholder="Nama modul, gunakan - jangan spasi" value="<?= $module['module'] ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_m_group" class="form-control-label">Group</label>
                                    <select name="edit_m_group" id="edit_m_group" class="form-control">
                                        <?php foreach($listGrup as $_grup) : ?>
                                            <option value="<?= md5(md5($_grup['id'])) ?>" <?= ($_grup['id'] == $module['group_id'])? "selected" : ""; ?>><?= $_grup['group']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_m_status" class="form-control-label">Status</label>
                                    <select name="edit_m_status" id="edit_m_status" class="form-control">
                                        <option value="-1" <?= ($module['status'] == -1)? "selected" : ""; ?>>Active</option>
                                        <option value="0" <?= ($module['status'] == 0)? "selected" : ""; ?>>Unactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_m_visibility" class="form-control-label">Visibility</label>
                                    <select name="edit_m_visibility" id="edit_m_visibility" class="form-control">
                                        <option value="-1" <?= ($module['visible'] == -1)? "selected" : ""; ?>>Show</option>
                                        <option value="0" <?= ($module['visible'] == 0)? "selected" : ""; ?>>Hidden</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="edit_m_order" class="form-control-label">Order</label>
                                    <input type="number" name="edit_m_order" id="edit_m_order" class="form-control" placeholder="Example: 1-10" <?= $module['m_order'] ?>>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2 text-end">
                                <button type="submit" class="btn btn-primary" data-original-text="Update">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-priamry">Form Tambah Permission</h5>
                </div>
                <div class="card-body">
                    <form method="post" id="form-create-permission">
                        <input type="hidden" name="module_id" value="<?= $moduleId ?>">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="code" class="form-control-label">Kode</label>
                                    <input type="text" name="code" class="form-control" placeholder="Ex: view, create, update, delete" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Nama</label>
                                    <input type="text" name="name" class="form-control" placeholder="Ex: View module" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="url" class="form-control-label">Url</label>
                                    <input type="text" name="url" class="form-control" placeholder="Ex: /<?= strtolower($module['group']) ?>/<?= strtolower($module['module']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2 text-end">
                                <button type="submit" class="btn btn-primary" data-original-text="Submit">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-priamry">Permissions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table-permission">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%">ID</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Nama</th>
                                    <th class="text-center">url</th>
                                    <th class="text-center" width="15%">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($permissionsss as $perm) : ?>
                                    <tr>
                                        <td><?= $perm['id']; ?></td>
                                        <td><?= $perm['code']; ?></td>
                                        <td><?= $perm['desc']; ?></td>
                                        <td><?= $perm['url']; ?></td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)" class="btn btn-sm btn-success update" data-id="<?= $perm['id'] ?>" data-code="<?= $perm['code'] ?>" data-name="<?= $perm['desc'] ?>" data-url="<?= $perm['url'] ?>">
                                                <i class="fas fa-edit text-white"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-danger ms-2 delete" data-id="<?= $perm['id'] ?>">
                                                <i class="fas fa-trash text-white"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="modal-edit-permission">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <form action="" method="post" id="form-update-permission">
                    <div class="modal-body">
                        <input type="hidden" name="module_id" value="<?= $moduleId ?>">
                        <input type="hidden" name="permission_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="code" class="form-control-label">Kode</label>
                                    <input type="text" name="code" class="form-control" placeholder="Ex: view, create, update, delete" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Nama</label>
                                    <input type="text" name="name" class="form-control" placeholder="Ex: View module" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="url" class="form-control-label">Url</label>
                                    <input type="text" name="url" class="form-control" placeholder="Ex: /<?= strtolower($module['group']) ?>/<?= strtolower($module['module']) ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" data-original-text="Update">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#table-permission').DataTable({
                scrollX: true,
                order: [[0, 'desc']],
                drawCallback: function() {
                    $('.update').on('click', function(el) {
                        let target = $(el.currentTarget), 
                            data = target.data(), 
                            modal = $('#modal-edit-permission');

                        modal.find('input[name="permission_id"]').val( data.id )
                        modal.find('input[name="code"]').val( data.code )
                        modal.find('input[name="name"]').val( data.name )
                        modal.find('input[name="url"]').val( data.url )
                        modal.modal('show');
                    })

                    $('.delete').on('click', function(el) {
                        let target = $(el.currentTarget),
                            data = target.data();

                        Swal.fire({
                            title: "Hapus permission",
                            text: "Apakah anda yakin ingin menghapus permission ini?",
                            icon: "question",
                            showCancelButton: true,
                            reverseButtons: true
                        }).then((result) => {
                            if(result.isConfirmed) {
                                Swal.fire({
                                    text: "Loading...",
                                    didOpen: function() {
                                        Swal.showLoading();
                                    }
                                })

                                $.post("/ajax/post/developer/module/deletePermission", data, (resp) => {
                                    Swal.fire(resp.alert).then(() => {
                                        if(resp.success) {
                                            location.reload();
                                        }
                                    })
                                }, 'json')
                            }
                        })
                    })
                }
            })

            $('#update-module').on('submit', function(event) {
                event.preventDefault();
                let button = $(this).find('button[type="submit"]'),
                    data = $(this).serialize();

                button.addClass("loading");
                $.post(`/ajax/post/developer/module/update`, data, (resp) => {
                    button.removeClass("loading");
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    })
                }, 'json')
            })

            $('#form-create-permission').on('submit', function(event) {
                event.preventDefault();
                let button = $(this).find('button[type="submit"]'),
                    data = $(this).serialize();

                button.addClass("loading");
                $.post(`/ajax/post/developer/module/createPermission`, data, (resp) => {
                    button.removeClass("loading");
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    })
                }, 'json')
            })

            $('#form-update-permission').on('submit', function(event) {
                event.preventDefault();
                let button = $(this).find('button[type="submit"]'),
                    data = $(this).serialize();

                button.addClass("loading");
                $.post(`/ajax/post/developer/module/updatePermission`, data, (resp) => {
                    $('#modal-edit-permission').modal('hide')
                    button.removeClass("loading");
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    })
                }, 'json')
            })
        })
    </script>
<?php endif; ?>