<?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "edit.symbol.category")){ ?>
    <div class="modal fade" tabindex="-1" id="modal-edit-category">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                </div>
                <form action="<?= $permisCreate['link'] ?>" method="post" id="form-edit-category">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="" class="form-label required">Category Name</label>
                            <input type="text" name="category_name" id="category_name" class="form-control" placeholder="Category Name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="xid" id="xid">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#form-edit-category').on('submit', function(event) {
                event.preventDefault();
                $('#modal-edit-category').modal('hide');
                let data = $(this).serialize(),
                    button = $(this).find('button[type="submit"]'),
                    url = "/ajax/post".concat($(this).attr('action'));

                
                Swal.fire({
                    title: 'Loading',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        $.post(url, data, (resp) => {
                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    location.reload();
                                }
                            })
                        }, 'json')
                    }
                });
            })
        })
    </script>
<?php }; ?>