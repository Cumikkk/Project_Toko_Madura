
<div class="card custom-card">
    <div class="card-header">
        <div class="d-flex justify-content-between mb-2">
            <h4>Symbol Detail</h4>
            <?php require_once __DIR__ . "/create_symboldetail.php"; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabel_symbol" class="table table-striped table-bordered text-nowrap">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Category</th>
                        <th style="vertical-align: middle" class="text-center">Symbol</th>
                        <th style="vertical-align: middle" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    let tabel_symbol;
    $(document).ready(function() {
        tabel_symbol = $('#tabel_symbol').DataTable({
            dom: 'Blfrtip',
            scrollX: true,
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
            ajax: {
                url: "/ajax/datatable/tools/symboldetail/view",
                contentType: "application/json",
                type: "GET"
            },
            columns: [
                { data: 'KATEGORI' },
                { data: 'SYMBOL' },
                { data: 'X', class: 'text-center' }
            ],
            order: [[0, 'asc']],
            lengthChange: false,
            drawCallback : () => {
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
                $('.hps-btn').on('click', function(e){
                    Swal.fire({
                        title: "Hapus symbol detail",
                        text: `Apa anda yakin akan menghapus symbol "${$(this).data('nme')}"`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Iya",
                        cancelButtonText: "Batalkan",
                        }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Loading',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                    $.post('/ajax/post/tools/symbol/delete_detail', {xdt: $(this).data('xid')}, (resp) => {
                                        Swal.fire(resp.alert).then(() => {
                                            if(resp.success) {
                                                location.reload();
                                            }
                                        })
                                    }, 'json')
                                }
                            });
                        }
                    });
                });
            }
        });
        
        try {
            const btnRef = tabel_symbol.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tabel_symbol.on('processing.dt', function (e, settings, processing) {
                const btn = tabel_symbol.button('refresh:name');
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

            tabel_symbol.on('xhr.dt', function () {
                const btn = tabel_symbol.button('refresh:name');
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
<?php require_once __DIR__ . "/edit_symboldetail.php"; ?>