
<div class="card custom-card">
    <div class="card-header">
        <div class="d-flex justify-content-between mb-2">
            <h4>Symbol Category</h4>
            <?php require_once __DIR__ . "/create_symbolcat.php"; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabel_symbolcategory" class="table table-striped table-bordered text-nowrap">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Name</th>
                        <th style="vertical-align: middle" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    let tabel_symbolcategory;
    $(document).ready(function() {
        tabel_symbolcategory = $('#tabel_symbolcategory').DataTable({
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
                url: "/ajax/datatable/tools/symbolcategory/view",
                contentType: "application/json",
                type: "GET"
            },
            columns: [
                { data: 'SYMBOL' },
                { data: 'JSNDT', class : 'text-center' }
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
            }
        });
        
        try {
            const btnRef = tabel_symbolcategory.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tabel_symbolcategory.on('processing.dt', function (e, settings, processing) {
                const btn = tabel_symbolcategory.button('refresh:name');
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

            tabel_symbolcategory.on('xhr.dt', function () {
                const btn = tabel_symbolcategory.button('refresh:name');
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
<?php require_once __DIR__ . "/edit_symbolcat.php"; ?>