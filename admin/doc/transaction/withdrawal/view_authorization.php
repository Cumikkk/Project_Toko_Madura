
<div class="card custom-card overflow-hidden">
    <div class="card-header">
        <h5 class="main-content-label mb-1">Authorization</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Date Range</label>
                <input type="text" id="filterDateRangeAuthorization" class="form-control" placeholder="Select date range" autocomplete="off" readonly>
                <input type="hidden" id="filterDateFromAuthorization">
                <input type="hidden" id="filterDateToAuthorization">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" id="btnResetFilterAuthorization" class="btn btn-secondary me-2">
                    <i class="fe fe-refresh-cw"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" width="100%" id="table-authorization">
                <thead>
                    <tr>
                        <th width="10%" style="vertical-align: middle" class="text-center">Date</th>
                        <th style="vertical-align: middle" class="text-center">Name</th>
                        <th style="vertical-align: middle" class="text-center">Login</th>
                        <th style="vertical-align: middle" class="text-center">Ticket</th>
                        <th style="vertical-align: middle" class="text-center">Bank Account</th>
                        <th style="vertical-align: middle" class="text-center">Amount</th>
                        <th style="vertical-align: middle" class="text-center">UTM</th>
                        <th style="vertical-align: middle" class="text-center" width="1%">#</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div id="myModalAuth" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="authorization-form">
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless">
                            <tr>
                                <td>Login</td>
                                <td><input type="text" id="auth-login" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Name</td>
                                <td><input type="text" id="auth-name" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr class="extr-elem">
                                <td>Amount</td>
                                <td><input type="text" id="auth-amntl" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr class="extr-elem">
                                <td>Rate</td>
                                <td><input type="text" id="auth-rate" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td><input type="text" id="auth-amnt" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Bank Destination</td>
                                <td>
                                    <div><input type="text" id="auth-bksrc" class="form-control text-dark" readonly></div>
                                    <div><input type="text" id="auth-bksrc-lst" class="form-control text-dark" readonly></div>
                                </td>
                            </tr>
                            <!-- <tr>
                                <td>Bank Destination</td>
                                <td><input type="text" id="auth-bkdst" class="form-control text-dark" readonly></td>
                            </tr> -->
                            <tr>
                                <td style="vertical-align: middle;">Note</td>
                                <td>
                                    <input type="text" class="form-control" autocomplete="off" name="note" value="-" required>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="auth-dpx" id="auth-dpx" value="" readonly required>
                    <input type="hidden" name="auth-act" id="auth-act" value="" readonly required>
                    <button type="submit" id="sbmtauth" style="display: none;"></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    let table_authorization;
    $(document).ready(() => {
        table_authorization = $('#table-authorization').DataTable({
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
				},
                {
                    text: 'Refresh',
                    name: 'refresh',
                    action: function (e, dt, node, config) {
                        const btn = dt.button('refresh:name');
                        const $node = $(btn.node());

                        if (!$node.data('original-text')) {
                            $node.data('original-text', $node.html());
                        }

                        btn.enable(false);
                        btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

                        dt.ajax.reload(null, false);
                    }
                }
			],
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/transaction/withdrawal_authorization",
                contentType: "application/json",
                type: "GET",
                data: function(d) {
                    d.filterDateFromAuthorization = $('#filterDateFromAuthorization').val();
                    d.filterDateToAuthorization = $('#filterDateToAuthorization').val();
                }
            }
        });
        
        try {
            const btnRef = table_authorization.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            table_authorization.on('processing.dt', function (e, settings, processing) {
                const btn = table_authorization.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                if (processing) {
                    btn.enable(false);
                    btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
                } else {
                    btn.enable(true);
                    const original = $node.data('original-text') || originalRefText;
                    btn.text(original);
                }
            });

            table_authorization.on('xhr.dt', function () {
                const btn = table_authorization.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }

        $('#filterDateRangeAuthorization').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' — ' + picker.endDate.format('YYYY-MM-DD'));
            $('#filterDateFromAuthorization').val(picker.startDate.format('YYYY-MM-DD'));
            $('#filterDateToAuthorization').val(picker.endDate.format('YYYY-MM-DD'));
            table_authorization.ajax.reload();
        }).on('cancel.daterangepicker', function() {
            $(this).val('');
            $('#filterDateFromAuthorization').val('');
            $('#filterDateToAuthorization').val('');
            table_authorization.ajax.reload();
        });

        // Reset filter button
        $('#btnResetFilterAuthorization').on('click', function() {
            $('#filterDateRangeAuthorization').val('');
            $('#filterDateFromAuthorization').val('');
            $('#filterDateToAuthorization').val('');
            table_authorization.ajax.reload();
        });
    });

    <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
        // Modifikasi event handler untuk tombol Accept/Reject
        $(document).ready(function() {
            if(table_authorization) {
                table_authorization.on('draw.dt', function() {
                    $.each($('#table-authorization tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(7);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.btn-action').length && actionArea.data('data')) {
                                let data = JSON.parse(atob(actionArea.data('data')));
                                let dataArray = [];
                                for(i in data) {
                                    if(i != "detail") {
                                        dataArray.push(`data-${i}="${data[i]}"`);
                                    }
                                }
    
                                actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-success btn-authorization" data-type="accept" ${dataArray.join(" ")}><i class="fas fa-check"></i></a>`);
                                actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-danger btn-authorization" data-type="reject" ${dataArray.join(" ")}><i class="fas fa-times"></i></a>`);
                                actionArea.append(`<button type="button" class="btn btn-info btn-sm edt-btn" data-bs-toggle="modal" data-bs-target="#myModalAuth" data-jsn="${btoa(data.detail)}"><i class="fas fa-info-circle"></i></button>`)
                            }
                        }
                    })

                    // Modifikasi event handler untuk tombol Accept/Reject
                    $('.btn-authorization').on('click', function(e){
                        // Simpan referensi ke semua tombol action
                        let button = $(e.currentTarget);
                        let data = button.data();
            
                        if(!data.code) {
                            Swal.fire("Error", "Invalida Code", "error");
                            return;
                        }
            
                        Swal.fire({
                            title: `${data.type} withdrawal request?`,
                            text: "Are you sure you want to proceed?",
                            icon: 'warning',
                            input: 'text',
                            inputLabel: 'Note',
                            inputPlaceholder: 'Note...',
                            inputAttributes: {
                                'aria-label': 'Note'
                            },
                            showCancelButton: true,
                            reverseButtons: true,
                            preConfirm: (note) => {
                                if(data.type == "reject" && !note) {
                                    Swal.showValidationMessage('Note is required for rejection.');
                                    return false;
                                }
            
                                return note;
                            }
                        }).then((result) => {
                            if(result.isConfirmed) {
                                Swal.fire({
                                    text: "Processing...",
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                })
            
                                $.post("/ajax/post/transaction/withdrawal_authorization", {note: result.value, type: data.type, 'auth-dpx': data.code}, (resp) => {
                                    Swal.fire(resp.alert).then(() => {
                                        if(resp.success) {
                                            reloadTable();
                                        }
                                    });
                                }, 'json')
                            }
                        })
                    });
            
                    $('.edt-btn').on('click', function(){
                        for(var [key, value] of Object.entries(JSON.parse(atob($(this).data('jsn'))))) {
                            if(key == 'auth-rate'){
                                $('.extr-elem').css('display', ((value == 0) ? 'none' : ''));
                            }
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
                })
            }
        })
    <?php endif; ?>
</script>