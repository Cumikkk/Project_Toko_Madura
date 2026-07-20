<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Country</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Country</li>
        </ol>
    </div>
    <div>
        <?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
            <a href="<?= $permisCreate['link'] ?>" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCountry"><i class="fas fa-plus"></i> Add</a>
        <?php endif; ?>
    </div>
</div>

<div class="card custom-card overflow-hidden">
    <div class="card-body">
        <div class="table-responsive">
            <table id="table" class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100">
                <thead>
                    <tr class="text-center">
                        <th>Country Name</th>
                        <th>Currency</th>
                        <th>Code</th>
                        <th>Phone Code</th>
                        <th width="15%">#</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>


<div class="modal" tabindex="-2" id="modalAddCountry" aria-labelledby="labell-modalAddCountry">
    <div class="modal-dialog modai-dialog-top">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" aria-label="label-modalAddCountry">Add Country</h5>
                <button type="button" class="btn btn-close"  data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form action="" method="post" id="form-add-country">
                <input type="hidden" name="country_id" id="country_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <labvel for="add-country-name" class="form-control-label">Country Name</labvel>
                                <input type="text" class="form-control" placeholder="Name..." id="add-country-name" name="add-country-name">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <labvel for="add-country-curr" class="form-control-label">Currency</labvel>
                                <input type="text" class="form-control" placeholder="Name..." id="add-country-curr" name="add-country-curr">
                            </div>
                        </div>
    
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-country-code" class="form-control-label">Code</label>
                                <input type="text" class="form-control" placeholder="Ex: USD, JPY, MYR" id="add-country-code" name="add-country-code">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-country-phone-code" class="form-control-label">Phone Code</label>
                                <input type="text" class="form-control" placeholder="Ex: USD, JPY, MYR" id="add-country-phone-code" name="add-country-phone-code">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer float-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit-add-country" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" tabindex="-2" id="modalEditCountry" aria-labelledby="labell-modalEditCountry">
    <div class="modal-dialog modai-dialog-top">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" aria-label="label-modalEditCountry">Edit Country</h5>
                <button type="button" class="btn btn-close"  data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form action="" method="post" id="form-update-country">
                <input type="hidden" name="country_id" id="country_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <labvel for="edit-country-name" class="form-control-label">Country Name</labvel>
                                <input type="text" class="form-control" placeholder="Name..." id="edit-country-name" name="edit-country-name">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <labvel for="edit-country-curr" class="form-control-label">Currency</labvel>
                                <input type="text" class="form-control" placeholder="Name..." id="edit-country-curr" name="edit-country-curr">
                            </div>
                        </div>
    
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-country-code" class="form-control-label">Code</label>
                                <input type="text" class="form-control" placeholder="Ex: USD, JPY, MYR" id="edit-country-code" name="edit-country-code">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-country-phone-code" class="form-control-label">Phone Code</label>
                                <input type="text" class="form-control" placeholder="Ex: USD, JPY, MYR" id="edit-country-phone-code" name="edit-country-phone-code">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer float-end">
                    <input type="hidden" name="country_id" id="mrx">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit-edit-country" id="submit-edit-country" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    let table;
    $(document).ready(function() {
        table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            scrollX: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/master/negara/view",
                contentType: "application/json",
                type: "GET",
            },
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            drawCallback: function() {
                $('.btn-edit').on('click', function(){
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
                            }
                        }else if($(`#${key}`)[0]?.tagName == 'SELECT' || $(`#${key}`)[0]?.tagName == 'BUTTON'){
                            $(`#${key}`).val(value);
                            // if($(`#${key}`).attr('id') == 'edt_head'){
                            //     $(`#${key}`)[0].dispatchEvent(new Event('change'));
                            //     console.log('dispatched');
                            // }
                            // dsptch();
                        }else if($(`#${key}`)[0]?.tagName == 'TEXTAREA'){
                            $(`#${key}`).html(value.replaceArray(["\\\\r\\\\n", "&amp;nbsp;"], ["&#13;&#10;", " "]));
                        }
                    }
                });

                $('.btn-delete').on('click', function(el) {
					let code = $(this).data('id');
					Swal.fire({
						title: "Are you sure?",
						text: "Are you sure to delete this country?",
						showCancelButton: true,
						icon: 'question',
						reverseButtons: true
					}).then((result) => {
						if(result.value) {
							if(code.length) {
								$.post("/ajax/post/master/negara/delete", {code: code}, function(resp) {
									Swal.fire(resp.alert).then(() => {
										if(resp.success) {
											location.reload();
										}
									})
								}, 'json')
							}
						}
					})
				})
            }
        });


        
        <?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
            $('#form-add-country').on('submit', function(event) {
                event.preventDefault();
    
                let data = Object.fromEntries(new FormData(this).entries());
                $.post("/ajax/post/master/negara/create", data, function(resp) {
                    $('#modalEditCountry').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    })
                }, 'json')
            });
        <?php endif; ?>

        $('#form-update-country').on('submit', function(event) {
            event.preventDefault();

            let data = Object.fromEntries(new FormData(this).entries());
            $.post("/ajax/post/master/negara/update", data, function(resp) {
                $('#modalEditCountry').modal('hide');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        });
    });
</script>