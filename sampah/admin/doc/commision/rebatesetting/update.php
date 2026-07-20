<?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "update")){ ?>

    <div class="modal fade" tabindex="-1" id="modal-update-rebate">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Rebate</h5>
                </div>
                <form action="<?= $permisCreate['link'] ?>" method="post" id="form-update-rebate">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rebate_structure" class="form-label required">Structure</label>
                            <select name="rebate_structure" id="edt_rebate_structure" class="form-control select2">
                                <?php
                                    $res = mysqli_query($db, "SELECT ID_SLSSTRC, SLSSTRC_NAME FROM tb_sales_structure WHERE SLSSTRC_UP IS NULL");
                                    while ($row = $res->fetch_assoc()) {
                                ?>
                                    <option value="<?= $row['ID_SLSSTRC'] ?>"><?= $row['SLSSTRC_NAME'] ?></option>
                                <?php }; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rebate_product" class="form-label required">Product</label>
                            <select name="rebate_product" id="edt_rebate_product" class="form-control select2">
                                <?php
                                    $res = mysqli_query($db, "SELECT ID_RTYPE, RTYPE_NAME, RTYPE_RATE, RTYPE_ISFLOATING, RTYPE_KOMISI FROM tb_racctype WHERE RTYPE_STS = -1");
                                    while ($row = $res->fetch_assoc()) {
                                ?>
                                    <option value="<?= $row['ID_RTYPE'] ?>"><?= $row['RTYPE_NAME'] ?> - <?= ($row['RTYPE_ISFLOATING'] == 0) ? number_format($row['RTYPE_RATE'], 0) : 'Floating'; ?> - <?= $row['RTYPE_KOMISI'] ?></option>
                                <?php }; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rebate_symbolcat" class="form-label required">Category</label>
                            <select name="rebate_symbolcat" id="edt_rebate_symbolcat" class="form-control select2">
                                <?php foreach(App\Models\Symbols::AllCategory() as $t) : ?>
                                    <option value="<?= $t['ID_SYMCAT'] ?>"><?= $t['SYMCAT_NAME'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rebate_amount" class="form-label required">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="rebate_amount" id="edt_rebate_amount" class="form-control" autocomplate="off" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="xedt" id="xedt">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
        $(document).ready(function() {
            $('#form-update-rebate').on('submit', function(event) {
                event.preventDefault();
                $('#modal-update-rebate').modal('hide');
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