<?php
    use App\Factory\FileUploadFactory;
    use App\Models\FileUpload;
?>
<?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "view_bank")){ ?>
    
    <div class="row row-sm">
        <div class="col-lg-12 col-md-12 col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-2">
                        <h4>Bank</h4>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalAddBankadm" class="btn btn-primary btn-sm"><i class="fas fa-plus me-3"></i>Add Bank</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped">
                            <thead>
                                <tr class="text-center">
                                    <th>Currency</th>
                                    <th>Name</th>
                                    <th>Holder</th>
                                    <th>Account</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sql_get_bank_admin = mysqli_query($db, "SELECT * FROM tb_bankadm"); ?>
                                <?php if($sql_get_bank_admin) : ?>
                                    <?php 
                                        while($bankadm = mysqli_fetch_assoc($sql_get_bank_admin)) : 
                                            $JSNDT = [
                                                "bkadm-name-edt" => $bankadm['BKADM_NAME'],
                                                "bkadm-curr-edt" => $bankadm['BKADM_CURR'],
                                                "bkadm-holder-edt" => $bankadm['BKADM_HOLDER'],
                                                "bkadm-account-edt" => $bankadm['BKADM_ACCOUNT'],
                                                "idbk" => md5(md5($bankadm['ID_BKADM'])),
                                            ];
                                    ?>
                                        <tr>
                                            <td><?php echo $bankadm['BKADM_CURR'] ?></td>
                                            <td><?php echo $bankadm['BKADM_NAME'] ?></td>
                                            <td><?php echo $bankadm['BKADM_HOLDER'] ?></td>
                                            <td><?php echo $bankadm['BKADM_ACCOUNT'] ?></td>
                                            <td class="text-center">
                                                <a href="javascript:void(0)" data-jsn="<?= base64_encode(json_encode($JSNDT)) ?>" data-bs-toggle="modal" data-bs-target="#modalEdtBankadm" class="btn btn-sm btn-success btn-edit-bkadm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" data-value="<?= md5(md5($bankadm['ID_BKADM'])); ?>" class="btn btn-sm btn-danger dltBtnBank"><i class="fas fa-trash"></i></a>
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

    <div class="modal fade" tabindex="-1" id="modalAddBankadm" aria-labelledby="label-modalAddBankadm">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Bank Admin</h5>
                    <button class="btn-close" aria-label="Close" data-bs-dismiss="modal">&times;</button>
                </div>

                <form action="" method="post" id="form-bank">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-name" class="form-label">Name</label>
                                    <select name="bkadm-name" id="bkadm-name" class="form-control" required>
                                        <option value="">Select</option>
                                        <?php $sql_get_bank_list = mysqli_query($db, "SELECT BANKLST_NAME FROM tb_banklist"); ?>
                                        <?php if($sql_get_bank_list) : ?>
                                            <?php while($banklst = mysqli_fetch_assoc($sql_get_bank_list)) : ?>
                                                <option value="<?= $banklst['BANKLST_NAME'] ?>"><?= $banklst['BANKLST_NAME'] ?></option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-curr" class="form-label">Currency</label>
                                    <select name="bkadm-curr" id="bkadm-curr" class="form-control" required>
                                        <option value="IDR">IDR</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-holder" class="form-label">Holder</label>
                                    <input type="text" class="form-control" name="bkadm-holder" id="bkadm-holder" placeholder="Bank Holder" value="<?php echo $COMPANY_PRF["COMPANY_NAME"] ?>" required>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-account" class="form-label">Account</label>
                                    <input type="number" class="form-control" name="bkadm-account" id="bkadm-account" placeholder="Bank Account" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" name="add-bankadm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="modalEdtBankadm" aria-labelledby="label-modalEdtBankadm">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Bank Admin</h5>
                    <button class="btn-close" aria-label="Close" data-bs-dismiss="modal">&times;</button>
                </div>

                <form action="" method="post" id="form-bank-edt">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-name" class="form-label">Name</label>
                                    <select name="bkadm-name" id="bkadm-name-edt" class="form-control" required>
                                        <option value="">Select</option>
                                        <?php $sql_get_bank_list = mysqli_query($db, "SELECT BANKLST_NAME FROM tb_banklist"); ?>
                                        <?php if($sql_get_bank_list) : ?>
                                            <?php while($banklst = mysqli_fetch_assoc($sql_get_bank_list)) : ?>
                                                <option value="<?= $banklst['BANKLST_NAME'] ?>"><?= $banklst['BANKLST_NAME'] ?></option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-curr" class="form-label">Currency</label>
                                    <select name="bkadm-curr" id="bkadm-curr-edt" class="form-control" required>
                                        <option value="IDR">IDR</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-holder" class="form-label">Holder</label>
                                    <input type="text" class="form-control" name="bkadm-holder" id="bkadm-holder-edt" placeholder="Bank Holder" value="<?php echo $COMPANY_PRF["COMPANY_NAME"] ?>" required>
                                </div>
                            </div>
        
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="bkadm-account" class="form-label">Account</label>
                                    <input type="number" class="form-control" name="bkadm-account" id="bkadm-account-edt" placeholder="Bank Account" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="idbk" id="idbk">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" name="edt-bankadm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(() => {
            $('.btn-edit-bkadm').on('click', function(){
                for(var [key, value] of Object.entries(JSON.parse(atob($(this).data('jsn'))))) {
                    if($(`#${key}`)[0]?.tagName == 'INPUT'){
                        if($(`#${key}`).attr('type') != 'file'){
                            if($(`#${key}`).attr('type') == 'checkbox'){
                                if((($(`#${key}`).prop('checked')) && $(`#${key}`).val() == value) || ((!$(`#${key}`).prop('checked')) && $(`#${key}`).val() != value)){
                                    $(`#${key}`)[0].click();
                                }
                            }else if($(`#${key}`).attr('class')?.includes('frmtRph')){
                                // console.log(value, $(`#${key}`));
                                $(`#${key}`).val(formatRupiah(value.toString()));
                            }else{ 
                                if($(`#${key}`).attr('type') != 'checkbox'){
                                    // console.log(key);
                                    $(`#${key}`).val(value); 
                                }
                            }
                        }else{
                            fln = (value !== null) ? `<?= FileUploadFactory::aws()->awsUrl().'/' ?>${value}` : 0;
                            if($(`.dropify-render`).children().lenght){
                                $(`.dropify-render`).children().attr('src', fln);
                            }else{
                                $(`.dropify-render`).html(`
                                    <img src="${fln}">
                                `);
                            }
                            $(`.dropify-filename-inner`).html(value);
                            $(`.dropify-preview`).css('display', 'block');
                        }
                    }else if($(`#${key}`)[0]?.tagName == 'SELECT' || $(`#${key}`)[0]?.tagName == 'BUTTON'){
                        $(`#${key}`).val(value);
                        // if($(`#${key}`).attr('id') == 'edt_head'){
                        //     $(`#${key}`)[0].dispatchEvent(new Event('change'));
                        //     console.log('dispatched');
                        // }
                        // dsptch();
                    }else if($(`#${key}`)[0]?.tagName == 'TEXTAREA'){
                        let rgx = new RegExp(`(?:&nbsp;|\\\\r\\\\n|$)`, "g");  
                        let val = htmlDecode(value.replaceAll(rgx, function(match, ett){
                            if(match == '\\r\\n'){
                                return '<br>';
                            }else if(ett == 213){
                                return ' ';
                            }else{ return ''; }
                        }));
                        $(`#${key}`).html(val);
                        $(`#${key}`).parent().find('.ql-editor').html(val);
                    }
                }
            });

            $('#form-bank').on('submit', function(ev){
                ev.preventDefault();
                $(this).find(':submit').addClass('loading');
                let data = Object.fromEntries(new FormData(this));
    
                $.post("/ajax/post/tools/profile_perushaaan/create_bank", data, function(resp) {
                    $(this).find(':submit').removeClass('loading');
                    $('#modalAddBankadm').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    });
                }, 'json');
            });

            $('#form-bank-edt').on('submit', function(ev){
                ev.preventDefault();
                $(this).find(':submit').addClass('loading');
                let data = Object.fromEntries(new FormData(this));
    
                $.post("/ajax/post/tools/profile_perushaaan/update_bank", data, function(resp) {
                    $(this).find(':submit').removeClass('loading');
                    $('#modalEdtBankadm').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    });
                }, 'json');
            });

            $('.dltBtnBank').on('click', function(e){
                Swal.fire({
                    title: `Delete Bank`,
                    text: `Are you sure to delete this Bank?`,
                    icon: 'question',
                    showCancelButton: true,
                    reverseButtons: true
                }).then((result) => {
                    if(result.isConfirmed) {
                        $.post("/ajax/post/tools/profile_perushaaan/delete_bank", {x: $(this).data('value')}, function(resp) {
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