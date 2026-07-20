<?php if($permisAccept = $adminPermissionCore->isHavePermission($moduleId, "action")) : ?>
    <div class="modal fade" id="modalBecomeIB" aria-labelledby="label-modalBecomeIB">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" aria-label="label-modalBecomeIB">Accept Request</h5>
                    <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal">&times;</button>
                </div>
                <form action="/ajax/post<?= $permisAccept['link'] ?>" method="post" id="form-edit-BecomeIB">
                    <div class="modal-body">
                        <input type="hidden" name="id" readonly required>
                        <input type="hidden" name="type" value="accept" readonly required>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="becomeib_name" class="form-control-label mb-0">Name</label>
                                <input type="text" name="becomeib_name" id="becomeib_name" class="form-control" readonly required></input>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="becomeib_email" class="form-control-label mb-0">Email</label>
                                <input type="text" name="becomeib_email" id="becomeib_email" class="form-control" readonly required></input>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="upline" class="form-control-label">Upline</label>
                            <select name="upline" id="upline" class="form-control select2-modal">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sales_type" class="form-control-label">Type</label>
                            <select name="sales_type" id="sales_type" class="form-control select2-modal">
                                <option value=""></option>
                            </select>
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
        let modal = $('#modalBecomeIB')
        $(document).ready(function() {
            modal.on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    dropdownParent: $('#modalBecomeIB')
                });
            });

            if(table_pending) {
                table_pending.on('draw.dt', function() {
                    $.each($('#table_pending tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(4);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.btn-update').length && actionArea.data('data')) {
                                let data = JSON.parse(atob(actionArea.data('data')));
                                let dataArray = [];
                                for(i in data) {
                                    dataArray.push(`data-${i}="${data[i]}"`);
                                }
                                actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-success btn-update" data-type="accept" ${dataArray.join(" ")}><i class="fas fa-edit"></i></a>`);
                            }
                        }
                    })

                    modal.find('select[name="upline"]').on('change', function() {
                        let type = this.value;
                        modal.find('select[name="sales_type"]').empty().append(new Option("No Type", "", false, false)).trigger('change');
                        $.post("/ajax/post/member/request_ib/get_type", {code: type}, (resp) => {
                            if(resp.success) {
                                $.each(resp.data, (i, val) => {
                                    modal.find('select[name="sales_type"]').append(new Option(`${val.name} - ${val.division_name}`, val.code, false, false));
                                })
                            }
                        }, 'json')
                    })

                    $('.btn-update').off('click').on('click', function(evt) {
                        let target = $(evt.currentTarget);
                        if(target) {
                            let data = target.data();
                            /** get Uplines */
                            modal.find('select[name="upline"]').empty().append(new Option("No Upline", "", false, false)).trigger('change');
                            $.post("/ajax/post/member/request_ib/get_upline", {code: data.code}, (resp) => {
                                if(resp.success) {
                                    $.each(resp.data, (i, val) => {
                                        let newOption = new Option(`${val.email} - ${val.type_name}`, val.code, val?.selected, val?.selected);
                                        modal.find('select[name="upline"]').append(newOption);
                                    })
                                }

                                modal.find('select[name="upline"]').trigger('change')
                                return resp;
                            }, 'json')

                            modal.find('input[name="id"]').val(data.id);
                            modal.find('input[name="becomeib_name"]').val(data.name);
                            modal.find('input[name="becomeib_email"]').val(data.email);
                            modal.modal('show');
                        }
                    })
                });
                
                $('#form-edit-BecomeIB').on('submit', function(event) {
                    event.preventDefault();
                    let target = $(event.currentTarget);
                    let button = target.find('button[type="submit"]');
                    let url = target.attr('action');
                    
                    let formData = target.serialize();

                    button.addClass('loading');
                    $.post(url, formData, function(resp) {
                        button.removeClass('loading');
                        modal.modal('hide');
                        Swal.fire(resp.alert).then(() => {
                            if (resp.success) {
                                table_pending.draw();
                            }
                        });
                    }, 'json');
                });
            }
        })
    </script>
<?php endif; ?>