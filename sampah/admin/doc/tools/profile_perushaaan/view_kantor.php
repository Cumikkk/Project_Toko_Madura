<?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "view_kantor")){ ?>
    
    <div class="row row-sm">
        <div class="col-lg-12 col-md-12 col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-2">
                        <h4>Kantor</h4>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalAddOffice" class="btn btn-primary btn-sm"><i class="fas fa-plus me-3"></i>Add Kantor</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Kota</th>
                                    <th>Alamat Lengkap</th>
                                    <th>Email</th>
                                    <th>Telepon Kantor</th>
                                    <th>Faksimile</th>
                                    <!-- <th>Iframe</th> -->
                                    <th class="text-center">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sql_get_office = mysqli_query($db, "SELECT * FROM tb_office"); ?>
                                <?php if($sql_get_office) : ?>
                                    <?php while($office = mysqli_fetch_assoc($sql_get_office)) : ?>
                                        <tr>
                                            <td><?php echo $office['OFC_CITY'] ?></td>
                                            <td width="30%"><?php echo $office['OFC_ADDRESS'] ?></td>
                                            <td><?php echo $office['OFC_PHONE'] ?></td>
                                            <td><?php echo $office['OFC_EMAIL'] ?></td>
                                            <td><?php echo $office['OFC_FAX'] ?></td>
                                            <!-- <td><a href=""><i>open</i></a></td> -->
                                            <td class="text-center" width="10%">
                                                <a href="javascript:void(0)" 
                                                    data-id="<?= md5(md5($office['ID_OFC'])); ?>"
                                                    data-city="<?= $office['OFC_CITY']; ?>" 
                                                    data-address="<?= $office['OFC_ADDRESS']; ?>" 
                                                    data-email="<?= $office['OFC_EMAIL']; ?>" 
                                                    data-phone="<?= $office['OFC_PHONE']; ?>" 
                                                    data-fax="<?= $office['OFC_FAX']; ?>" 
                                                    data-iframe="<?= $office['OFC_IFRAME']; ?>"
                                                    class="btn btn-sm btn-success btn-edit"><i class="fas fa-edit"></i></a>
                                                <a href="javascript:void(0)" data-value="<?= md5(md5($office['ID_OFC'])); ?>" class="btn btn-sm btn-danger dltBtn"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="modalAddOffice" aria-labelledby="label-modalAddOffice">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kantor</h5>
                    <button class="btn-close" aria-label="Close" data-bs-dismiss="modal">&times;</button>
                </div>
                <form action="" method="post" id="form-add-kantor">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="ofc_city" class="form-label">Kota</label>
                                    <input type="text" class="form-control" name="ofc_city" id="ofc_city" placeholder="Nama Kota" required>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="ofc_address" class="form-label">Address</label>
                                    <input type="text" class="form-control" name="ofc_address" id="ofc_address" placeholder="Alamat Kantor" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="ofc_phone" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" name="ofc_phone" id="ofc_phone" placeholder="No. Telepon kantor" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="ofc_fax" class="form-label">Faksimile</label>
                                    <input type="text" class="form-control" name="ofc_fax" id="ofc_fax" placeholder="No. Faksimile kantor" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="ofc_email" class="form-label">Email</label>
                                    <input type="text" class="form-control" name="ofc_email" id="ofc_email" placeholder="Email Kantor" required>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="content" class="form-label">Iframe <small class="text-danger">* Hanya Link Embed saja</small></label>
                                    <textarea name="content" id="content" rows="10" class="form-control" placeholder="Contoh: https://www.google.com/maps/embed?"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" name="add-office">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="modalEditOffice" aria-labelledby="label-modalEditOffice">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kantor</h5>
                    <button class="btn-close" aria-label="Close" data-bs-dismiss="modal">&times;</button>
                </div>
                <form action="" method="post" id="form-edt-kantor">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_ofc_city" class="form-label">Kota</label>
                                    <input type="text" class="form-control" name="ofc_city" id="edit_ofc_city" placeholder="Nama Kota" required>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_ofc_address" class="form-label">Address</label>
                                    <input type="text" class="form-control" name="ofc_address" id="edit_ofc_address" placeholder="Alamat Kantor" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_ofc_phone" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" name="ofc_phone" id="edit_ofc_phone" placeholder="No. Telepon kantor" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_ofc_fax" class="form-label">Faksimile</label>
                                    <input type="text" class="form-control" name="ofc_fax" id="edit_ofc_fax" placeholder="No. Faksimile kantor" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_ofc_email" class="form-label">Email</label>
                                    <input type="text" class="form-control" name="ofc_email" id="edit_ofc_email" placeholder="Email Kantor" required>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_content" class="form-label">Iframe <small class="text-danger">* Hanya Link Embed saja</small></label>
                                    <textarea name="content" id="edit_content" rows="10" class="form-control" placeholder="Contoh: https://www.google.com/maps/embed?"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="edit-office" id="edit-office">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(() => {

            $('#form-add-kantor').on('submit', function(ev){
                ev.preventDefault();
                $(this).find(':submit').addClass('loading');
                let data = Object.fromEntries(new FormData(this));
    
                $.post("/ajax/post/tools/profile_perushaaan/create_kantor", data, function(resp) {
                    $(this).find(':submit').removeClass('loading');
                    $('#modalAddOffice').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    });
                }, 'json');
            });


            $('.btn-edit').on('click', function(btn) {
                $('#edit_ofc_city').val($(btn.currentTarget).data('city'))
                $('#edit_ofc_address').val($(btn.currentTarget).data('address'))
                $('#edit_ofc_phone').val($(btn.currentTarget).data('phone'))
                $('#edit_ofc_email').val($(btn.currentTarget).data('email'))
                $('#edit_content').val($(btn.currentTarget).data('iframe'))
                $('#edit-office').val($(btn.currentTarget).data('id'))
                $('#edit_ofc_fax').val($(btn.currentTarget).data('fax'))
                $('#modalEditOffice').modal('show')
            });
            $('#form-edt-kantor').on('submit', function(ev){
                ev.preventDefault();
                $(this).find(':submit').addClass('loading');
                let data = Object.fromEntries(new FormData(this));
    
                $.post("/ajax/post/tools/profile_perushaaan/update_kantor", data, function(resp) {
                    $('#modalEditOffice').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    });
                }, 'json');
                $(this).find(':submit').removeClass('loading');
            });

            
            $('.dltBtn').on('click', function(e){
                Swal.fire({
                    title: `Delete Office`,
                    text: `Are you sure to delete this Office?`,
                    icon: 'question',
                    showCancelButton: true,
                    reverseButtons: true
                }).then((result) => {
                    if(result.isConfirmed) {
                        $.post("/ajax/post/tools/profile_perushaaan/delete_kantor", {x: $(this).data('value')}, function(resp) {
                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    location.reload();
                                }
                            })
                        }, 'json');
                    }
                });
            });
        });
    </script>
<?php } ?>