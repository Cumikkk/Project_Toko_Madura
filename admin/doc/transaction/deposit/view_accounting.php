<div class="card custom-card overflow-hidden">
    <div class="card-header">
        <h5 class="main-content-label mb-1">Accounting</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label for="segregatedAccountFilterAccounting" class="form-label">Filter Segregated Account</label>
                <select name="segregatedAccountFilterAccounting" id="segregatedAccountFilterAccounting" class="form-control" required>
                    <option value="">Semua</option>
                    <?php $sqlGetBank = $db->query("SELECT * FROM tb_bankadm"); ?>
                    <?php if($sqlGetBank) : ?>
                        <?php foreach($sqlGetBank->fetch_all(MYSQLI_ASSOC) as $row) : ?>
                            <option value="<?= implode("/", [$row['BKADM_NAME'], $row['BKADM_HOLDER'], $row['BKADM_ACCOUNT']]) ?>"><?= implode("/", [$row['BKADM_NAME'], $row['BKADM_HOLDER'], $row['BKADM_ACCOUNT']]) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <input type="text" id="filterDateRangeAccounting" class="form-control" placeholder="Select date range" autocomplete="off" readonly>
                <input type="hidden" id="filterDateFromAccounting">
                <input type="hidden" id="filterDateToAccounting">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" id="btnResetFilterAccounting" class="btn btn-secondary me-2">
                    <i class="fe fe-refresh-cw"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" width="100%" id="table-accounting">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Date</th>
                        <th style="vertical-align: middle" class="text-center">Name</th>
                        <th style="vertical-align: middle" class="text-center">Login</th>
                        <th style="vertical-align: middle" class="text-center">Bank Account</th>
                        <th style="vertical-align: middle" class="text-center">Segregated Account</th>
                        <th style="vertical-align: middle" class="text-center">Amount</th>
                        <th style="vertical-align: middle" class="text-center">Pic</th>
                        <th style="vertical-align: middle" class="text-center">UTM</th>
                        <th style="vertical-align: middle" class="text-center" width="1%">#</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div id="myModalAcc" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="accounting-form">
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless">
                            <tr>
                                <td>Login</td>
                                <td><input type="text" id="acc-login" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Name</td>
                                <td><input type="text" id="acc-name" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr class="extr-elem">
                                <td>Amount</td>
                                <td><input type="text" id="acc-amntl" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr class="extr-elem">
                                <td>Rate</td>
                                <td><input type="text" id="acc-rate" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Amount Received</td>
                                <td><input type="text" id="acc-amnt" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Bank Source</td>
                                <td>
                                    <div><input type="text" id="acc-bksrc" class="form-control text-dark" readonly></div>
                                    <div><input type="text" id="acc-bksrc-lst" class="form-control text-dark" readonly></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Bank Destination</td>
                                <td>
                                    <div><input type="text" id="acc-bkdst" class="form-control text-dark" readonly></div>
                                    <div><input type="text" id="acc-bkdst-lst" class="form-control text-dark" readonly></div>
                                </td>
                            </tr>
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
                    <input type="hidden" name="acc-dpx" id="acc-dpx" value="" readonly required>
                    <input type="hidden" name="acc-act" id="acc-act" value="" readonly required>
                    <button type="submit" id="sbmtacc" style="display: none;"></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    let table_accounting;
    $(document).ready(() => {
        table_accounting = $('#table-accounting').DataTable({
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
                url: "/ajax/datatable/transaction/accounting",
                contentType: "application/json",
                type: "GET",
                data: function(d) {
                    d.segregatedAccountFilterAccounting = $('#segregatedAccountFilterAccounting').val();
                    d.filterDateFrom = $('#filterDateFromAccounting').val();
                    d.filterDateTo = $('#filterDateToAccounting').val();
                }
            },
            columnDefs: [
                { targets: 4, className: "text-end" },
            ]
        });

        try {
            const btnRef = table_accounting.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            table_accounting.on('processing.dt', function (e, settings, processing) {
                const btn = table_accounting.button('refresh:name');
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

            table_accounting.on('xhr.dt', function () {
                const btn = table_accounting.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }

        $('#segregatedAccountFilterAccounting').on('change', function() {
            table_accounting.ajax.reload();
        });
        
        $('#filterDateRangeAccounting').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' — ' + picker.endDate.format('YYYY-MM-DD'));
            $('#filterDateFromAccounting').val(picker.startDate.format('YYYY-MM-DD'));
            $('#filterDateToAccounting').val(picker.endDate.format('YYYY-MM-DD'));
            table_accounting.ajax.reload();
        }).on('cancel.daterangepicker', function() {
            $(this).val('');
            $('#filterDateFromAccounting').val('');
            $('#filterDateToAccounting').val('');
            table_accounting.ajax.reload();
        });

        // Reset filter button
        $('#btnResetFilterAccounting').on('click', function() {
            $('#segregatedAccountFilterAccounting').val('');
            $('#filterDateRangeAccounting').val('');
            $('#filterDateFromAccounting').val('');
            $('#filterDateToAccounting').val('');
            table_accounting.ajax.reload();
        });
    });


    <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
        // Modifikasi event handler untuk tombol Accept/Reject
        $(document).ready(function() {
            if(table_accounting) {
                table_accounting.on('draw.dt', function() {
                    $.each($('#table-accounting tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(8);
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
    
                                actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-success btn-accept btn-action" data-type="accept"  ${dataArray.join(" ")}><i class="fas fa-check"></i></a>`);
                                actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-danger btn-reject btn-action" data-type="reject"  ${dataArray.join(" ")}><i class="fas fa-times"></i></a>`);
                                actionArea.append(`<button type="button" class="btn btn-info btn-sm edt-btn" data-bs-toggle="modal" data-bs-target="#myModalAcc" data-jsn="${btoa(data.detail)}"><i class="fas fa-info-circle"></i></button>`);
                            }
                        }
                    })

                    $('.btn-accept, .btn-reject').on('click', function(e){
                        // Simpan referensi ke semua tombol action
                        let button = $(e.currentTarget);
                        let data = button.data();

                        if(!data.code) {
                            Swal.fire("Error", "Invalida Code", "error");
                            return;
                        }

                        Swal.fire({
                            title: `${data.type} deposit request?`,
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

                                $.post("/ajax/post/transaction/accounting", {note: result.value, type: data.type, 'acc-dpx': data.code}, (resp) => {
                                    Swal.fire(resp.alert).then(() => {
                                        if(resp.success) {
                                            table_accounting.ajax.reload();
                                            if(table_history) {
                                                table_history.ajax.reload();
                                            }
                                        }
                                    });
                                }, 'json')
                            }
                        })
                    });

                    $('.edt-btn').on('click', function(){
                        for(var [key, value] of Object.entries(JSON.parse(atob($(this).data('jsn'))))) {
                            if(key == 'acc-rate'){
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