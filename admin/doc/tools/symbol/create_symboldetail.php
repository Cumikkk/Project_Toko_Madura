<?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "create.detail.symboldetail")){ ?>
    
    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-create-symbol" class="btn btn-primary btn-sm btn-icon-text"><i class="fas fa-plus"></i> Add Symbol</a>
    <div class="modal fade" tabindex="-1" id="modal-create-symbol">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Symbol</h5>
                </div>
                <form action="<?= $permisCreate['link'] ?>" method="post" id="form-create-symbol">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="" class="form-label required">Category Name</label>
                            <select name="symbol_category" id="type" class="form-control select2">
                                <?php foreach(App\Models\Symbols::AllCategory() as $t) : ?>
                                    <option value="<?= $t['ID_SYMCAT'] ?>"><?= $t['SYMCAT_NAME'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="" class="form-label required">Symbol Name</label>
                            <input type="text" name="symbol_name" class="form-control" placeholder="Symbol Name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#form-create-symbol').on('submit', function(event) {
                event.preventDefault();
                let data = $(this).serialize(),
                    button = $(this).find('button[type="submit"]'),
                    url = "/ajax/post".concat($(this).attr('action'));

                button.addClass('loading');
                $.post(url, data, (resp) => {
                    $('#modal-create-symbol').modal('hide')
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    })
                }, 'json')
            })
        })
    </script>
<?php }; ?>