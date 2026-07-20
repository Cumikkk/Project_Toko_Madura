<?php
    use App\Models\Helper;

?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">History</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">APUPPT</a></li>
            <li class="breadcrumb-item"><a href="#">Enhanced Due Dillingence (EDD)</a></li>
            <li class="breadcrumb-item active" aria-current="page">History</li>
        </ol>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header font-weight-bold">History EDD</div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="table" class="table table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Date Time</th>
                        <th style="vertical-align: middle" class="text-center">Nama</th>
                        <th style="vertical-align: middle" class="text-center">NIK</th>
                        <th style="vertical-align: middle" class="text-center">Risiko Jumlah Rekening Transaksi Milik Nasabah</th>
                        <th style="vertical-align: middle" class="text-center">Risiko Total Deposit (Top-up) per hari</th>
                        <th style="vertical-align: middle" class="text-center">Risiko Total Equity Nasabah</th>
                        <th style="vertical-align: middle" class="text-center">Faktor Lainnya</th>
                        <th style="vertical-align: middle" class="text-center">Analisa Dan Rekomendasi</th>
                        <th style="vertical-align: middle" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        let prm = {mrx : `<?= Helper::form_input($_GET["d"]) ?>`};
        let tbl = $('#table').DataTable( {
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
            ajax: {
                url: "/ajax/datatable/apuppt/edd_history",
                contentType: "application/json",
                type: "GET",
                data: function (d) {
                    return  $.extend(d, prm);
                }
            },
            lengthMenu: [[10, 50, 100], [10, 50, 100]],
            scrollX: true,
            order: [[ 0, "desc" ]]
        } );
        try {
            const btnRef = tbl.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tbl.on('processing.dt', function (e, settings, processing) {
                const btn = tbl.button('refresh:name');
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

            tbl.on('xhr.dt', function () {
                const btn = tbl.button('refresh:name');
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