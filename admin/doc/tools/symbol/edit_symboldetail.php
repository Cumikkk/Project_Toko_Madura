<?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "edit.detail.symboldetail")){ ?>
    
    <div class="modal fade" tabindex="-1" id="modal-edit-symbol">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Symbol</h5>
                </div>
                <form action="<?= $permisCreate['link'] ?>" method="post" id="form-edit-symbol">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="" class="form-label required">Category Name</label>
                            <select name="symbol_category" id="edit_type" class="form-control select2">
                                <?php foreach(App\Models\Symbols::AllCategory() as $t) : ?>
                                    <option value="<?= $t['ID_SYMCAT'] ?>"><?= $t['SYMCAT_NAME'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="" class="form-label required">Symbol Name</label>
                            <input type="text" name="symbol_name" id="symbol_name" class="form-control" placeholder="Symbol Name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="xdt" id="xdt">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#form-edit-symbol').on('submit', function(event) {
                event.preventDefault();
                $('#modal-edit-symbol').modal('hide');
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
                        }, 'json');
                    }
                });
            })
        })
    </script>
<?php }; ?>