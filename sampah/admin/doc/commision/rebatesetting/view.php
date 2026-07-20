
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Rebate Setting</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Commision</li>
			<li class="breadcrumb-item active" aria-current="page">Rebate Setting</li>
		</ol>
	</div>
    <div class="d-flex">
        <div class="justify-content-center">
            <?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "create")){ ?>
                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-create-rebate" class="btn btn-primary my-2 me-2"><i class="fas fa-plus"></i> Add Rebate Setting</a>
            <?php } ?> 
        </div>
    </div>
</div>
<div class="card custom-card overflow-hidden">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" width="100%" id="rebate_setting">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Commision</th>
                        <th style="vertical-align: middle" class="text-center">Sturcture</th>
                        <th style="vertical-align: middle" class="text-center">Product</th>
                        <th style="vertical-align: middle" class="text-center">Rate</th>
                        <th style="vertical-align: middle" class="text-center">Symbol Category</th>
                        <th style="vertical-align: middle" class="text-center">Amount</th>
                        <th style="vertical-align: middle" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?php 
if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "create")){
    require_once __DIR__ . "/create.php";
};
if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "update")){
    require_once __DIR__ . "/update.php";
};
?>

<script>
    let rebate_setting;
    $(document).ready(function() {
        rebate_setting = $('#rebate_setting').DataTable({
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
                url: "/ajax/datatable/commision/rebatesetting/view",
                contentType: "application/json",
                type: "GET"
            },
            columns: [
                { data: 'RTYPE_KOMISI', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 0, '') },
                { data: 'SLSSTRC_NAME' },
                { data: 'RTYPE_NAME' },
                { data: 'RTYPE_RATE', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 0, '') },
                { data: 'SYMCAT_NAME' },
                { data: 'COMMSET_AMOUNT', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 2, '') },
                { data: 'X'}
            ],
            order: [[1, 'asc']],
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
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
                        }else if($(`#${key}`)[0]?.tagName == 'SELECT'){
                            $(`#${key}`).val(value).trigger('change');
                        }else if($(`#${key}`)[0]?.tagName == 'TEXTAREA'){
                            $(`#${key}`).html(value.replaceArray(["\\\\r\\\\n", "\\\\n", "&amp;nbsp;", "\\\\"], ["&#13;&#10;", "&#13;", " ", ""]));
                        }
                    }
                });
                $('.hps-btn').on('click', function(e){
                    Swal.fire({
                        title: "Hapus setting rebate",
                        text: `Apa anda yakin akan menghapus setting rebate "${$(this).data('spc')}"`,
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
                                    $.post('/ajax/post/commision/rebatesetting/delete', {xid: $(this).data('val')}, (resp) => {
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
            const btnRef = rebate_setting.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            rebate_setting.on('processing.dt', function (e, settings, processing) {
                const btn = rebate_setting.button('refresh:name');
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

            rebate_setting.on('xhr.dt', function () {
                const btn = rebate_setting.button('refresh:name');
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