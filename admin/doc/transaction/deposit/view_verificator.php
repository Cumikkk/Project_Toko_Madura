<div class="card custom-card overflow-hidden">
    <div class="card-header">
        <h5 class="main-content-label mb-1">Verificator</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" width="100%" id="table-verificator">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Date</th>
                        <th style="vertical-align: middle" class="text-center">Name</th>
                        <th style="vertical-align: middle" class="text-center">Email</th>
                        <th style="vertical-align: middle" class="text-center">Login</th>
                        <th style="vertical-align: middle" class="text-center">Amount</th>
                        <th style="vertical-align: middle" class="text-center">Pic</th>
                        <th style="vertical-align: middle" class="text-center" width="1%">#</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="verificatior-form">
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless">
                            <tr>
                                <td>Login</td>
                                <td><input type="text" id="ver-login" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Name</td>
                                <td><input type="text" id="ver-name" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td><input type="text" id="ver-email" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr class="extr-elem">
                                <td>Amount</td>
                                <td><input type="text" id="ver-amntl" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr class="extr-elem">
                                <td>Rate</td>
                                <td><input type="text" id="ver-rate" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td><input type="text" id="ver-amnt" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Bank Source</td>
                                <td><input type="text" id="ver-bksrc" class="form-control text-dark" readonly></td>
                            </tr>
                            <tr>
                                <td>Bank Destination</td>
                                <td><input type="text" id="ver-bkdst" class="form-control text-dark" readonly></td>
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
                    <input type="hidden" name="ver-dpx" id="ver-dpx" value="" readonly required>
                    <input type="hidden" name="ver-act" id="ver-act" value="" readonly required>
                    <button type="submit" id="sbmtVer" style="display: none;"></button>
                    <button type="button" value="accept" class="btn btn-success act-btn">Accept</button>
                    <button type="button" value="reject" class="btn btn-danger act-btn">Reject</button>
                </div>
            </form>
        </div>
    
    </div>
</div>
<script>
    $(document).ready(() => {
        // Modifikasi event handler untuk tombol Accept/Reject
        $('.act-btn').on('click', function(e){
            // Simpan referensi ke semua tombol action
            const $allButtons = $('.act-btn');
            
            // Disable semua tombol action dan tambahkan spinner ke tombol yang diklik
            $allButtons.prop('disabled', true);
            const $clickedButton = $(this);
            const originalText = $clickedButton.text();
            $clickedButton.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + originalText);
            
            // Set value dan submit form
            $('#ver-act').val($clickedButton.val());
            
            // Submit form dengan AJAX manual untuk lebih banyak kontrol
            let data = Object.fromEntries(new FormData($('#verificatior-form')[0]));
            $.ajax({
                url: "/ajax/post/transaction/verificator",
                type: "POST",
                data: data,
                dataType: 'json',
                success: function(resp) {
                    $('#myModal').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            table_verificator.ajax.reload();
                            if(table_accounting) {
                                table_accounting.ajax.reload();
                            }
                            $allButtons.prop('disabled', false);
                            $clickedButton.html(originalText);
                        } else {
                            // Kembalikan tombol ke keadaan normal jika terjadi error pada server
                            $allButtons.prop('disabled', false);
                            $clickedButton.html(originalText);
                        }
                    });
                },
                error: function() {
                    // Kembalikan tombol ke keadaan normal jika terjadi error jaringan
                    $allButtons.prop('disabled', false);
                    $clickedButton.html(originalText);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process request. Please try again.'
                    });
                }
            });
        });

        // Hapus event submit form karena kita handling langsung di tombol action
        $('#verificatior-form').on('submit', function(e){
            e.preventDefault();
        });
        
        table_verificator = $('#table-verificator').DataTable({
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
                url: "/ajax/datatable/transaction/verificator",
                contentType: "application/json",
                type: "GET"
            },
            drawCallback : () => {
                $('.edt-btn').on('click', function(){
                    for(var [key, value] of Object.entries(JSON.parse(atob($(this).data('jsn'))))) {
                        if(key == 'ver-rate'){
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
            }
        });
        try {
            const btnRef = table_verificator.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            table_verificator.on('processing.dt', function (e, settings, processing) {
                const btn = table_verificator.button('refresh:name');
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

            table_verificator.on('xhr.dt', function () {
                const btn = table_verificator.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }
    });
</script>