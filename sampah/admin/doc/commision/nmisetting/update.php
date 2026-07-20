<?php if($permisDelete = $adminPermissionCore->isHavePermission($moduleId, "update")){ ?>
    <div class="modal fade" tabindex="-1" id="modalEditNMISetting" aria-labelledby="label-modalEditNMISetting">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" aria-label="label-modalEditNMISetting">Edit NMI</h5>
                    <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal">&times;</button>
                </div>
                <form action="/ajax/post/commision/nmisetting/update" method="post" id="form-edit-nmisetting">
                    <div class="modal-body">
                        <input type="hidden" name="id" class="form-control" readonly required>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nmi_target" class="form-control-label mb-0 required">Target</label>
                                <input type="number" min="10000" step="10000" id="nmi_target" name="nmi_target" class="form-control" required>
                            </div>
        
                            <div class="col-md-6 mb-3">
                                <label for="nmi_percent" class="form-control-label mb-0 required">Percent</label>
                                <input type="number" min="0.01" step="0.01" id="nmi_percent" name="nmi_percent" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-end">
                        <button type="button" class="btnClose btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            if(table_history) {
                let modal = $('#modalEditNMISetting')
                table_history.on('draw.dt', function() {
                    $.each($('#table_history tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(4);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('btn-update').length) {
                                let data = JSON.parse(atob(actionArea.data('data')));
                                let dataArray = [];
                                for(i in data) {
                                    dataArray.push(`data-${i}="${data[i]}"`);
                                }
                                actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-success btn-update" ${dataArray.join(" ")}><i class="fas fa-edit"></i></a>`);
                            }
                        }
                    })

                    $('.btn-update').on('click', function(evt) {
                        let target = $(evt.currentTarget);
                        if(target) {
                            let data = target.data();
                            
                            modal.find('input[name="id"]').val(data.id);
                            modal.find('input[name="nmi_target"]').val(data.nmi_target);
                            modal.find('input[name="nmi_percent"]').val(data.nmi_percent);
                            modal.modal('show');
                        }
                    })
                })
                
                $(modal).on('hide.bs.modal', function() {
                    $.each($(modal).find('input'), (i, el) => {
                        el.value = '';
                    })
                })

                $('#form-edit-nmisetting').on('submit', function(event) {
                    event.preventDefault();

                    let postData = Object.fromEntries(new FormData(this).entries());
                    let button = $(this).find('button[type="submit"]');
                    let url = $(this).attr('action');

                    button.addClass('loading');
                    $.post(url, postData, function(resp) {
                        button.removeClass('loading');
                        modal.modal('hide');
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                table_history.draw();
                            }
                        })
                    }, 'json')
                })
            }
        })
    </script>
<?php }; ?>