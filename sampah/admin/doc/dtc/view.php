
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Development Test Case</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item active" aria-current="page">Development Test Case</li>
		</ol>
	</div>
</div>


<div class="row row-sm">
	<div class="col-lg-12">
		<div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">History</h5>
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modallAddTest" class="btn btn-primary float-end btn-sm"><i class="fas fa-plus"></i> Add Test</a>
                </div>    
            </div>
			<div class="card-body">
				<div class="table-responsive">
					<table id="table" class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100" >
                        <thead>
                            <tr>
                                <th class="text-center">Date Time</th>
                                <th class="text-center">ID</th>
                                <th class="text-center">Test Case</th>
                                <th class="text-center">Desc</th>
                                <th class="text-center">Pre Condition</th>
                                <th class="text-center">Testing Steps</th>
                                <th class="text-center">Expected Result</th>
                                <th class="text-center">Actual Result</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">#</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="modallAddTest" aria-labelledby="label-modallAddTest">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" aria-label="label-modallAddTest">Form Add DTC</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <form action="" method="post" id="formAdd">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_case" class="form-label">Test Case</label>
                                <input type="text" name="add_case" id="add_case" class="form-control" placeholder="Test Case" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_desc" class="form-label">Description</label>
                                <textarea class="form-control" name="add_desc" id="add_desc"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_precondition" class="form-label">Pre Condition</label>
                                <input type="text" name="add_precondition" id="add_precondition" class="form-control" placeholder="Pre Condition" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_steps" class="form-label">Steps</label>
                                <select name="add_steps" id="add_steps" class="form-control">
                                    <option value="Requirement Analysis">Requirement Analysis</option>
                                    <option value="test plan">Test Plan</option>
                                    <option value="test design">Test Design</option>
                                    <option value="environment set-up">Environment Set-up</option>
                                    <option value="test execution">Test Execution</option>
                                    <option value="test closure">Test Closure</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_result" class="form-label">Result</label>
                                <input type="text" name="add_result" id="add_result" class="form-control" placeholder="Result" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_actual_result" class="form-label">Actual Result</label>
                                <input type="text" name="add_actual_result" id="add_actual_result" class="form-control" placeholder="Actual Result" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_notes" class="form-label">Notes</label>
                                <textarea class="form-control" name="add_notes" id="add_notes"></textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_status" class="form-label">Status</label>
                                <select class="form-control" name="add_status" id="add_status">
                                    <option value="-1">Berhasil</option>
                                    <option value="1">Gagal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit-add-test" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="modalEditTest" aria-labelledby="label-modalEditTest">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" aria-label="label-modalEditTest">Form Edit DTC</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <form action="" method="post" id="formEdt">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_case" class="form-label">Test Case</label>
                                <input type="text" name="edit_case" id="edit_case" class="form-control" placeholder="Test Case" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_desc" class="form-label">Description</label>
                                <textarea class="form-control" name="edit_desc" id="edit_desc"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_precondition" class="form-label">Pre Condition</label>
                                <input type="text" name="edit_precondition" id="edit_precondition" class="form-control" placeholder="Pre Condition" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_steps" class="form-label">Steps</label>
                                <select name="edit_steps" id="edit_steps" class="form-control">
                                    <option value="Requirement Analysis">Requirement Analysis</option>
                                    <option value="test plan">Test Plan</option>
                                    <option value="test design">Test Design</option>
                                    <option value="environment set-up">Environment Set-up</option>
                                    <option value="test execution">Test Execution</option>
                                    <option value="test closure">Test Closure</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_result" class="form-label">Result</label>
                                <input type="text" name="edit_result" id="edit_result" class="form-control" placeholder="Result" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_actual_result" class="form-label">Actual Result</label>
                                <input type="text" name="edit_actual_result" id="edit_actual_result" class="form-control" placeholder="Actual Result" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_notes" class="form-label">Notes</label>
                                <textarea class="form-control" name="edit_notes" id="edit_notes"></textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-control" name="edit_status" id="edit_status">
                                    <option value="-1">Berhasil</option>
                                    <option value="1">Gagal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="submit-edit-test" id="submit-edit-test">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit-edit-test-btn" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        String.prototype.replaceArray = function(find, replace) {
            var replaceString = this;
            var regex; 
            for (var i = 0; i < find.length; i++) {
                regex = new RegExp(find[i], "g");
                replaceString = replaceString.replace(regex, replace[i]);
            }
            return replaceString;
        };

        $('#formAdd').on('submit', function(ev){
            ev.preventDefault();
            let data = Object.fromEntries(new FormData(this));
            $.post("/ajax/post/dtc/create", data, function(resp) {
                $('#modallAddTest').modal('hide');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                });
            }, 'json');
        });
        
        $('#formEdt').on('submit', function(ev){
            ev.preventDefault();
            let data = Object.fromEntries(new FormData(this));
            $.post("/ajax/post/dtc/update", data, function(resp) {
                $('#modalEditTest').modal('hide');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                });
            }, 'json');
        });

        $('#table').DataTable({
            dom: 'Blfrtip',
            processing: true,
            serverSide: true,
            deferRender: true,
			buttons: [
				{
					extend: 'excel',
					text: 'Excel',
				},
				{
					extend: 'copy',
					text: 'Copy'
				}
			],
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/dtc/view",
                contentType: "application/json",
                type: "GET"
            },
            drawCallback: () => {
                $('.dltBtn').on('click', function(e){
                    Swal.fire({
                        title: `Delete DTC`,
                        text: `Are you sure to delete this DTC?`,
                        icon: 'question',
                        showCancelButton: true,
                        reverseButtons: true
                    }).then((result) => {
                        if(result.isConfirmed) {
                            $.post("/ajax/post/dtc/delete", {x: $(this).data('value')}, function(resp) {
                                Swal.fire(resp.alert).then(() => {
                                    if(resp.success) {
                                        location.reload();
                                    }
                                })
                            }, 'json');
                        }
                    });
                });

                $('.edt-btn').on('click', function(){
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
                            $(`#${key}`).html(value.replaceArray(["\\\\r\\\\n", "\\\\n", "&amp;nbsp;", "\\\\"], ["&#13;&#10;", "&#13;", " ", ""]));
                        }
                    }
                });
            }
        });
    });
</script>