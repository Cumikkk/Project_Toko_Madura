<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Evaluasi Calon Nasabah</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">APUPPT</a></li>
            <li class="breadcrumb-item active" aria-current="page">Evaluasi Calon Nasabah</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <h5 class="main-content-label mb-1">Calon Nasabah Yang Belum Di Evaluasi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="table table-striped table-hover" width="100%">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle" class="text-center">Date Time</th>
                                <th style="vertical-align: middle" class="text-center">Nama</th>
                                <th style="vertical-align: middle" class="text-center">NIK</th>
                                <th style="vertical-align: middle" class="text-center">Tanggal Lahir</th>
                                <th style="vertical-align: middle" class="text-center">Email</th>
                                <th style="vertical-align: middle" class="text-center">Status Konfirmasi WPB</th>
                                <th style="vertical-align: middle" class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <h5 class="main-content-label mb-1">History Evaluasi Calon Nasabah</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table2" class="table table-striped table-hover" width="100%">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle" class="text-center">Date Time</th>
                                <th style="vertical-align: middle" class="text-center">Nama</th>
                                <th style="vertical-align: middle" class="text-center">NIK</th>
                                <th style="vertical-align: middle" class="text-center">Tanggal Lahir</th>
                                <th style="vertical-align: middle" class="text-center">Email</th>
                                <th style="vertical-align: middle" class="text-center">Status Konfirmasi WPB</th>
                                <th style="vertical-align: middle" class="text-center">Tingkat Risiko</th>
                                <th style="vertical-align: middle" class="text-center">Konfirmasi APUPPT</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        let prm = {};
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
                url: "/ajax/datatable/apuppt/evaluasi_calon_nasabah/view",
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

        
        table_history = $('#table2').DataTable({
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
                url: "/ajax/datatable/apuppt/history_evaluasi_calon_nasabah",
                contentType: "application/json",
                type: "GET"
            },
        });
        try {
            const btnRef = table_history.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            table_history.on('processing.dt', function (e, settings, processing) {
                const btn = table_history.button('refresh:name');
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

            table_history.on('xhr.dt', function () {
                const btn = table_history.button('refresh:name');
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